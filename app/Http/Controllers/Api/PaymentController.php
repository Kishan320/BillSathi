<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentRequest;
use App\Models\Contact;
use App\Models\Payment;
use App\Services\BankLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private BankLedgerService $ledger) {}

    public function index(Request $request)
    {
        $query = Payment::with(['contact', 'bankAccount'])->where('user_id', $request->user()->id);
        if ($request->search) $query->where('reference', 'like', "%{$request->search}%");
        if ($request->type) $query->where('type', $request->type);
        return response()->json($query->orderByDesc('date')->paginate(20));
    }

    public function store(PaymentRequest $request)
    {
        $userId = $request->user()->id;
        $data = $request->validated();
        $data['user_id'] = $userId;

        $payment = DB::transaction(function () use ($userId, $data) {
            $payment = Payment::create($data);

            $this->applyContactSettlement($userId, $payment, null);
            $this->syncLedger($userId, $payment);
            return $payment;
        });

        return response()->json($payment, 201);
    }

    public function show(Request $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        return response()->json($payment->load(['contact', 'bankAccount']));
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;
        $data = $request->validated();

        DB::transaction(function () use ($userId, $payment, $data) {
            $before = $payment->replicate();
            $payment->update($data);
            $payment->refresh();
            $this->applyContactSettlement($userId, $payment, $before);
            $this->syncLedger($userId, $payment);
        });

        return response()->json($payment->refresh());
    }

    public function destroy(Request $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;
        DB::transaction(function () use ($userId, $payment) {
            // revert contact settlement effect
            $this->applyContactSettlement($userId, null, $payment);

            // remove ledger row
            $ledgerType = $payment->type === 'received' ? 'deposit' : 'purchase_payment';
            $direction = $payment->type === 'received' ? 'credit' : 'debit';
            $this->ledger->upsertForSource($userId, $ledgerType, null, $direction, 0, now()->toDateString(), $payment);
            $payment->delete();
        });
        return response()->json(['message' => 'Deleted']);
    }

    private function syncLedger(int $userId, Payment $payment): void
    {
        $bankAccountId = $payment->bank_account_id ? (int) $payment->bank_account_id : null;
        $amount = (float) $payment->amount;
        $date = (string) $payment->date;

        if ($payment->type === 'received') {
            $this->ledger->upsertForSource(
                $userId,
                'deposit',
                $bankAccountId,
                'credit',
                $amount,
                $date,
                $payment,
                null,
                $payment->reference ? "Payment received: {$payment->reference}" : null
            );
            return;
        }

        $this->ledger->upsertForSource(
            $userId,
            'purchase_payment',
            $bankAccountId,
            'debit',
            $amount,
            $date,
            $payment,
            null,
            $payment->reference ? "Payment sent: {$payment->reference}" : null
        );
    }

    /**
     * Adjust contact opening balance as a simple "pending amount" settlement.
     * This keeps the "Pending Amount" UX working even without invoice allocation.
     */
    private function applyContactSettlement(int $userId, ?Payment $after, ?Payment $before): void
    {
        // Revert previous effect
        if ($before && $before->contact_id) {
            $this->applySingleSettlement(
                $userId,
                (int) $before->contact_id,
                $before->type,
                (float) $before->amount,
                true
            );
        }

        // Apply new effect
        if ($after && $after->contact_id) {
            $this->applySingleSettlement(
                $userId,
                (int) $after->contact_id,
                $after->type,
                (float) $after->amount,
                false
            );
        }
    }

    private function applySingleSettlement(int $userId, int $contactId, string $paymentType, float $amount, bool $revert): void
    {
        $contact = Contact::query()
            ->where('user_id', $userId)
            ->whereKey($contactId)
            ->lockForUpdate()
            ->first();

        if (!$contact) return;

        // Only settle when payment direction matches contact pending direction
        $settlesPayable = $paymentType === 'sent' && $contact->opening_balance_type === 'payable';
        $settlesReceivable = $paymentType === 'received' && $contact->opening_balance_type === 'receivable';

        if (!$settlesPayable && !$settlesReceivable) return;

        $delta = $revert ? -$amount : $amount; // revert adds back, apply subtracts
        $current = (float) $contact->opening_balance;

        // opening_balance is always stored positive; we just reduce (or add back on revert)
        $updated = max(0, $current - $delta);
        $contact->update(['opening_balance' => $updated]);
    }
}
