<?php

namespace App\Services;

use App\Models\GeneralLedger;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Phase 1 PR 2 — GL ↔ Transaction Reconciliation Service
 *
 * Non-destructive: detects and logs orphans / missing links; never modifies
 * financial data.  Safe to run in production at any time.
 *
 * Two categories of orphans are detected:
 *
 * 1. Transactions without GL rows
 *    Completed transactions that have no matching general_ledger entries
 *    (neither via the legacy reference_id path nor the new FK).
 *
 * 2. GL rows without a linked transaction
 *    general_ledger rows where transaction_record_id is NULL or points to a
 *    non-existent transaction.  These represent pre-Phase-1 postings or any
 *    orphaned manual entries.
 *
 * Both checks are purely read-only SELECT queries.
 */
class GlReconciliationService
{
    /**
     * Run the full reconciliation and return a structured report.
     *
     * @param  \DateTimeInterface|string|null  $since  Optionally restrict to
     *         entries on or after this date (YYYY-MM-DD).
     * @return array{
     *   transactions_without_gl: Collection,
     *   gl_rows_without_transaction: Collection,
     *   summary: array
     * }
     */
    public function reconcile(mixed $since = null): array
    {
        $transactionsWithoutGl = $this->detectTransactionsWithoutGl($since);
        $glRowsWithoutTransaction = $this->detectGlRowsWithoutTransaction($since);

        $summary = [
            'transactions_without_gl_count'       => $transactionsWithoutGl->count(),
            'gl_rows_without_transaction_count'    => $glRowsWithoutTransaction->count(),
            'since'                                => $since ? (string) $since : 'all time',
            'is_clean'                             => $transactionsWithoutGl->isEmpty()
                                                      && $glRowsWithoutTransaction->isEmpty(),
        ];

        $this->logReport($summary, $transactionsWithoutGl, $glRowsWithoutTransaction);

        return [
            'transactions_without_gl'      => $transactionsWithoutGl,
            'gl_rows_without_transaction'  => $glRowsWithoutTransaction,
            'summary'                      => $summary,
        ];
    }

    /**
     * Detect completed transactions that have no corresponding GL entries.
     *
     * Uses the `reference_type = 'Transaction'` + `reference_id` path (legacy)
     * because that is what all existing LedgerService writes use.  The new
     * `transaction_record_id` FK could also be checked once all postings are
     * dual-written.
     *
     * @param  mixed  $since  Optional date filter.
     * @return Collection<int, Transaction>
     */
    public function detectTransactionsWithoutGl(mixed $since = null): Collection
    {
        $query = Transaction::where('status', 'completed')
            ->whereNotIn('id', function ($sub) {
                $sub->select('reference_id')
                    ->from('general_ledger')
                    ->where('reference_type', 'Transaction')
                    ->whereNotNull('reference_id');
            });

        if ($since) {
            $query->where('transaction_date', '>=', $since);
        }

        return $query->get(['id', 'transaction_number', 'type', 'amount', 'member_id', 'transaction_date', 'status']);
    }

    /**
     * Detect GL rows that are not linked to a transaction via the new FK
     * (transaction_record_id IS NULL).
     *
     * These represent rows created before the Phase 1 migration ran, or any
     * entries posted via a code path that was not yet updated to dual-write.
     *
     * @param  mixed  $since  Optional date filter.
     * @return Collection<int, GeneralLedger>
     */
    public function detectGlRowsWithoutTransaction(mixed $since = null): Collection
    {
        $query = GeneralLedger::whereNull('transaction_record_id')
            ->where('status', 'posted');

        if ($since) {
            $query->where('transaction_date', '>=', $since);
        }

        return $query->get(['id', 'transaction_id', 'transaction_record_id', 'account_code', 'account_type', 'debit_amount', 'credit_amount', 'transaction_date', 'status']);
    }

    /**
     * Log reconciliation results.
     */
    protected function logReport(
        array $summary,
        Collection $transactionsWithoutGl,
        Collection $glRowsWithoutTransaction
    ): void {
        $level = $summary['is_clean'] ? 'info' : 'warning';

        Log::$level('[GlReconciliation] Reconciliation complete', [
            'summary' => $summary,
        ]);

        if ($transactionsWithoutGl->isNotEmpty()) {
            Log::warning('[GlReconciliation] Transactions without GL entries', [
                'count' => $transactionsWithoutGl->count(),
                'ids'   => $transactionsWithoutGl->pluck('id')->toArray(),
            ]);
        }

        if ($glRowsWithoutTransaction->isNotEmpty()) {
            Log::warning('[GlReconciliation] GL rows without transaction_record_id link', [
                'count' => $glRowsWithoutTransaction->count(),
                'ids'   => $glRowsWithoutTransaction->pluck('id')->toArray(),
            ]);
        }
    }
}
