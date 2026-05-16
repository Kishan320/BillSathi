<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentRequest;
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
            $payment->update($data);
            $payment->refresh();
            $this->syncLedger($userId, $payment);
        });

        return response()->json($payment->refresh());
    }

    public function destroy(Request $request, Payment $payment)
    {
        abort_if($payment->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;
        DB::transaction(function () use ($userId, $payment) {
            $this->ledger->upsertForSource($userId, 'purchase_payment', null, 'debit', 0, now()->toDateString(), $payment);
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
}
