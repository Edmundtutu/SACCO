<?php

namespace App\Console\Commands;

use App\Services\GlReconciliationService;
use Illuminate\Console\Command;

/**
 * Phase 1 PR 2 — GL Reconciliation Artisan Command
 *
 * Non-destructive: reports orphaned transactions and GL rows to stdout and
 * the application log.  Never modifies financial data.
 *
 * Usage:
 *   php artisan reconcile:gl
 *   php artisan reconcile:gl --since=2026-01-01
 *   php artisan reconcile:gl --json
 */
class ReconcileGlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reconcile:gl
                            {--since= : Only check entries on or after this date (YYYY-MM-DD)}
                            {--json   : Output the full report as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect GL ↔ Transaction linkage gaps (non-destructive reporting only)';

    public function __construct(protected GlReconciliationService $reconciler)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $since = $this->option('since') ?: null;

        $this->info('Running GL ↔ Transaction reconciliation' . ($since ? " since {$since}" : '') . ' …');

        $report = $this->reconciler->reconcile($since);

        if ($this->option('json')) {
            $this->line(json_encode([
                'summary'                      => $report['summary'],
                'transactions_without_gl'      => $report['transactions_without_gl']->toArray(),
                'gl_rows_without_transaction'  => $report['gl_rows_without_transaction']->toArray(),
            ], JSON_PRETTY_PRINT));

            return $report['summary']['is_clean'] ? self::SUCCESS : self::FAILURE;
        }

        $summary = $report['summary'];

        $this->newLine();
        $this->line('  <comment>Summary</comment>');
        $this->line("  Period          : {$summary['since']}");
        $this->line("  Transactions without GL entries : {$summary['transactions_without_gl_count']}");
        $this->line("  GL rows without transaction FK  : {$summary['gl_rows_without_transaction_count']}");
        $this->newLine();

        if ($summary['is_clean']) {
            $this->info('✅  Ledger is clean — no orphans detected.');
            return self::SUCCESS;
        }

        if ($report['transactions_without_gl']->isNotEmpty()) {
            $this->warn('⚠  Transactions without GL entries:');
            $this->table(
                ['ID', 'Transaction #', 'Type', 'Amount', 'Date'],
                $report['transactions_without_gl']->map(fn ($t) => [
                    $t->id,
                    $t->transaction_number,
                    $t->type,
                    $t->amount,
                    $t->transaction_date,
                ])->toArray()
            );
        }

        if ($report['gl_rows_without_transaction']->isNotEmpty()) {
            $this->warn('⚠  GL rows without transaction_record_id:');
            $this->table(
                ['GL ID', 'transaction_id (string)', 'Account Code', 'Debit', 'Credit', 'Date'],
                $report['gl_rows_without_transaction']->map(fn ($g) => [
                    $g->id,
                    $g->transaction_id,
                    $g->account_code,
                    $g->debit_amount,
                    $g->credit_amount,
                    $g->transaction_date,
                ])->toArray()
            );
        }

        return self::FAILURE;
    }
}
