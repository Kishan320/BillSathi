<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BankLedgerService
{
    /**
     * Upsert a single ledger entry for a source document (expense/income/payment/etc.).
     * Pass $bankAccountId = null to remove the ledger entry.
     */
    public function upsertForSource(
        int $userId,
        string $type,
        ?int $bankAccountId,
        string $direction,
        float $amount,
        string $occurredOn,
        Model $source,
        ?string $sourceKey = null,
        ?string $notes = null,
        array $meta = []
    ): void {
        DB::transaction(function () use (
            $userId,
            $type,
            $bankAccountId,
            $direction,
            $amount,
            $occurredOn,
            $source,
            $sourceKey,
            $notes,
            $meta
        ) {
            $existing = BankTransaction::query()
                ->where('user_id', $userId)
                ->where('source_type', $source::class)
                ->where('source_id', $source->getKey())
                ->where('source_key', $sourceKey)
                ->first();

            $touchedAccountIds = [];

            if (!$bankAccountId) {
                if ($existing) {
                    $touchedAccountIds[] = $existing->bank_account_id;
                    $existing->delete();
                }

                $this->recalculateBalances($userId, $touchedAccountIds);
                return;
            }

            if ($existing && (int) $existing->bank_account_id !== (int) $bankAccountId) {
                $touchedAccountIds[] = $existing->bank_account_id;
            }

            $touchedAccountIds[] = $bankAccountId;

            BankTransaction::updateOrCreate(
                [
                    'user_id' => $userId,
                    'source_type' => $source::class,
                    'source_id' => $source->getKey(),
                    'source_key' => $sourceKey,
                ],
                [
                    'bank_account_id' => $bankAccountId,
                    'type' => $type,
                    'direction' => $direction,
                    'amount' => $amount,
                    'occurred_on' => $occurredOn,
                    'notes' => $notes,
                    'meta' => $meta ?: null,
                ]
            );

            $this->recalculateBalances($userId, $touchedAccountIds);
        });
    }

    /**
     * Recalculate and persist current_balance for accounts.
     */
    public function recalculateBalances(int $userId, array $bankAccountIds): void
    {
        $bankAccountIds = array_values(array_unique(array_filter($bankAccountIds)));
        if (!$bankAccountIds) return;

        $rows = BankTransaction::query()
            ->selectRaw("
                bank_account_id,
                SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END) AS credits,
                SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END) AS debits
            ")
            ->where('user_id', $userId)
            ->whereIn('bank_account_id', $bankAccountIds)
            ->groupBy('bank_account_id')
            ->get()
            ->keyBy('bank_account_id');

        foreach ($bankAccountIds as $accountId) {
            $credits = (float) ($rows[$accountId]->credits ?? 0);
            $debits = (float) ($rows[$accountId]->debits ?? 0);
            $balance = $credits - $debits;

            BankAccount::query()
                ->where('user_id', $userId)
                ->whereKey($accountId)
                ->update(['current_balance' => $balance]);
        }
    }
}

