<?php

namespace App\Services;

use App\Models\GeneralLedger;
use App\Models\Transaction;
use App\DTOs\LedgerEntryDTO;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Create general ledger entries for a transaction
     */
    public function createLedgerEntries(Transaction $transaction, array $ledgerEntries): void
    {
        $batchId = Str::uuid();
        $entryIndex = 1;

        foreach ($ledgerEntries as $entry) {
            $this->createLedgerEntry($transaction, $entry, $batchId, $entryIndex);
            $entryIndex++;
        }

        // Verify entries balance
        $this->verifyBatchBalance($batchId);
    }

    /**
     * Create a single ledger entry
     */
    protected function createLedgerEntry(
        Transaction    $transaction,
        LedgerEntryDTO $entry,
        string         $batchId,
        int            $entryIndex
    ): void
    {
        GeneralLedger::create([
            'transaction_id' => 'GL-' . $transaction->id . '-' . $entryIndex,
            'transaction_date' => $transaction->transaction_date->toDateString(),
            'account_code' => $entry->accountCode,
            'account_name' => $entry->accountName,
            'account_type' => $entry->accountType,
            'debit_amount' => $entry->debitAmount,
            'credit_amount' => $entry->creditAmount,
            'description' => $entry->description,
            'reference_type' => 'Transaction',
            'reference_id' => $transaction->id,
            'member_id' => $transaction->member_id,
            'batch_id' => $batchId,
            'status' => 'posted',
            'posted_by' => $transaction->processed_by,
            'posted_at' => now(),
        ]);
    }

    /**
     * Create reversal entries for a reversed transaction
     */
    public function createReversalEntries(Transaction $originalTransaction, Transaction $reversalTransaction): void
    {
        $originalEntries = $originalTransaction->generalLedgerEntries;
        $batchId = Str::uuid();
        $entryIndex = 1;

        foreach ($originalEntries as $originalEntry) {
            GeneralLedger::create([
                'transaction_id' => 'GL-' . $reversalTransaction->id . '-' . $entryIndex,
                'transaction_date' => $reversalTransaction->transaction_date->toDateString(),
                'account_code' => $originalEntry->account_code,
                'account_name' => $originalEntry->account_name,
                'account_type' => $originalEntry->account_type,
                'debit_amount' => $originalEntry->credit_amount, // Swap debits and credits
                'credit_amount' => $originalEntry->debit_amount,
                'description' => 'REVERSAL: ' . $originalEntry->description,
                'reference_type' => 'Transaction',
                'reference_id' => $reversalTransaction->id,
                'member_id' => $originalEntry->member_id,
                'batch_id' => $batchId,
                'status' => 'posted',
                'posted_by' => $reversalTransaction->processed_by,
                'posted_at' => now(),
            ]);
            $entryIndex++;
        }
    }

    /**
     * Verify that a batch of entries balances (debits = credits)
     */
    protected function verifyBatchBalance(string $batchId): void
    {
        $batch = GeneralLedger::where('batch_id', $batchId)->get();
        $totalDebits = $batch->sum('debit_amount');
        $totalCredits = $batch->sum('credit_amount');

        if (abs($totalDebits - $totalCredits) > 0.01) {
            throw new \Exception("Ledger batch {$batchId} is out of balance. Debits: {$totalDebits}, Credits: {$totalCredits}");
        }
    }

    /**
     * Get trial balance for a specific date
     */
    public function getTrialBalance(\DateTime $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();

        $query = GeneralLedger::where('status', 'posted')
            ->where('transaction_date', '<=', $asOfDate->format('Y-m-d'));

        $entries = $query->selectRaw('
            account_code,
            account_name,
            account_type,
            SUM(debit_amount) as total_debits,
            SUM(credit_amount) as total_credits
        ')
            ->groupBy('account_code', 'account_name', 'account_type')
            ->get();

        $trialBalance = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($entries as $entry) {
            $balance = $entry->total_debits - $entry->total_credits;

            $trialBalance[] = [
                'account_code' => $entry->account_code,
                'account_name' => $entry->account_name,
                'account_type' => $entry->account_type,
                'debit_balance' => $balance > 0 ? $balance : 0,
                'credit_balance' => $balance < 0 ? abs($balance) : 0,
            ];

            if ($balance > 0) {
                $totalDebits += $balance;
            } else {
                $totalCredits += abs($balance);
            }
        }

        return [
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'accounts' => $trialBalance,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01
        ];
    }
}
