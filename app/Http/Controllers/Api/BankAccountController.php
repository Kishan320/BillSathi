<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BankAccountRequest;
use App\Http\Resources\Api\BankAccountResource;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Services\BankLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct(private BankLedgerService $ledger)
    {
    }

    public function index(Request $request)
    {
        $query = BankAccount::query()
            ->where('user_id', $request->user()->id);

        if ($request->filled('search')) {
            $s = trim((string) $request->search);
            $query->where('name', 'like', "%{$s}%");
        }

        $includeClosed = filter_var($request->get('include_closed', false), FILTER_VALIDATE_BOOL);
        if (!$includeClosed) {
            $query->where('status', 'active');
        }

        return BankAccountResource::collection(
            $query->orderBy('name')->get()
        );
    }

    public function store(BankAccountRequest $request)
    {
        $userId = $request->user()->id;
        $data = $request->validated();

        $openingBalance = (float) ($data['opening_balance'] ?? 0);

        $metadata = $this->buildMetadata($data);

        $account = DB::transaction(function () use ($userId, $data, $openingBalance, $metadata) {
            $account = BankAccount::create([
                'user_id' => $userId,
                'name' => $data['name'],
                'account_type' => $data['account_type'],
                'currency' => strtoupper($data['currency']),
                'opening_balance' => $openingBalance,
                'current_balance' => 0, // recalculated after ledger insert
                'status' => 'active',
                'metadata' => $metadata ?: null,
                // legacy support
                'type' => $this->legacyTypeFromAccountType($data['account_type']),
                'account_number' => $metadata['account_number'] ?? null,
            ]);

            // Opening balance is a first-class ledger entry
            BankTransaction::create([
                'user_id' => $userId,
                'bank_account_id' => $account->id,
                'type' => 'opening_balance',
                'direction' => $openingBalance >= 0 ? 'credit' : 'debit',
                'amount' => abs($openingBalance),
                'occurred_on' => now()->toDateString(),
                'notes' => 'Opening balance',
                'source_type' => $account::class,
                'source_id' => $account->id,
                'source_key' => 'opening',
            ]);

            $this->ledger->recalculateBalances($userId, [$account->id]);

            return $account->refresh();
        });

        return (new BankAccountResource($account))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        return new BankAccountResource($bankAccount);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;
        $data = $request->validated();

        $openingBalanceProvided = array_key_exists('opening_balance', $data);
        $openingBalance = (float) ($data['opening_balance'] ?? $bankAccount->opening_balance);

        $metadata = $this->buildMetadata($data, $bankAccount->metadata ?? []);

        DB::transaction(function () use ($userId, $bankAccount, $data, $openingBalanceProvided, $openingBalance, $metadata) {
            $bankAccount->update([
                'name' => $data['name'] ?? $bankAccount->name,
                'account_type' => $data['account_type'] ?? $bankAccount->account_type,
                'currency' => isset($data['currency']) ? strtoupper($data['currency']) : $bankAccount->currency,
                'metadata' => $metadata ?: null,
                // legacy support
                'type' => isset($data['account_type']) ? $this->legacyTypeFromAccountType($data['account_type']) : $bankAccount->type,
                'account_number' => $metadata['account_number'] ?? $bankAccount->account_number,
            ]);

            if ($openingBalanceProvided) {
                $bankAccount->update(['opening_balance' => $openingBalance]);

                BankTransaction::query()
                    ->where('user_id', $userId)
                    ->where('bank_account_id', $bankAccount->id)
                    ->where('source_type', $bankAccount::class)
                    ->where('source_id', $bankAccount->id)
                    ->where('source_key', 'opening')
                    ->update([
                        'direction' => $openingBalance >= 0 ? 'credit' : 'debit',
                        'amount' => abs($openingBalance),
                    ]);
            }

            $this->ledger->recalculateBalances($userId, [$bankAccount->id]);
        });

        return new BankAccountResource($bankAccount->refresh());
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        $userId = $request->user()->id;

        $hasLedgerActivity = BankTransaction::query()
            ->where('user_id', $userId)
            ->where('bank_account_id', $bankAccount->id)
            ->whereNot(function ($q) use ($bankAccount) {
                $q->where('source_type', $bankAccount::class)
                    ->where('source_id', $bankAccount->id)
                    ->where('source_key', 'opening');
            })
            ->exists();

        $hasLinkedDocs =
            DB::table('expenses')->where('user_id', $userId)->where('bank_account_id', $bankAccount->id)->exists() ||
            DB::table('incomes')->where('user_id', $userId)->where('bank_account_id', $bankAccount->id)->exists() ||
            DB::table('payments')->where('user_id', $userId)->where('bank_account_id', $bankAccount->id)->exists() ||
            DB::table('purchases')->where('user_id', $userId)->where('bank_account_id', $bankAccount->id)->exists() ||
            DB::table('sales')->where('user_id', $userId)->where('bank_account_id', $bankAccount->id)->exists() ||
            DB::table('settlements')->where('user_id', $userId)->where('bank_account_id', $bankAccount->id)->exists() ||
            DB::table('loans')->where('user_id', $userId)->where('bank_account_id', $bankAccount->id)->exists() ||
            DB::table('bank_transfers')->where('user_id', $userId)->where(function ($q) use ($bankAccount) {
                $q->where('from_account_id', $bankAccount->id)->orWhere('to_account_id', $bankAccount->id);
            })->exists();

        if ($hasLedgerActivity || $hasLinkedDocs) {
            return response()->json([
                'message' => 'Cannot delete this account because it has linked transactions.',
                'errors' => [
                    'bank_account' => ['Account has linked transactions and cannot be deleted.'],
                ],
            ], 422);
        }

        DB::transaction(function () use ($userId, $bankAccount) {
            // remove opening ledger row as well
            BankTransaction::query()
                ->where('user_id', $userId)
                ->where('bank_account_id', $bankAccount->id)
                ->delete();

            $bankAccount->delete();
        });

        return response()->json(['message' => 'Deleted']);
    }

    public function close(Request $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->user_id !== $request->user()->id, 403);
        if ($bankAccount->status === 'closed') {
            return new BankAccountResource($bankAccount);
        }

        $bankAccount->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return new BankAccountResource($bankAccount->refresh());
    }

    private function legacyTypeFromAccountType(string $accountType): string
    {
        return match ($accountType) {
            'bank' => 'bank',
            'cash' => 'cash',
            'credit_card' => 'credit_card',
            default => 'other',
        };
    }

    private function buildMetadata(array $data, array $existing = []): array
    {
        $metadata = $existing;

        $fields = [
            'bank_name',
            'account_holder_name',
            'account_number',
            'ifsc_swift_code',
            'branch_name',
            'last_4_digits',
            'billing_cycle_date',
            'wallet_provider_name',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if ($value === null || $value === '')
                    unset($metadata[$field]);
                else
                    $metadata[$field] = $value;
            }
        }

        return $metadata;
    }
}
