<?php

namespace App\Services;

use App\Models\GeneralLedger;
use App\Models\LoanRepayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Phase 1 PR 4 — GL Baseline Check Service
 *
 * Provides read-only validation utilities for GL-derived financial reports.
 * All methods are non-destructive: they only run SELECT queries and return
 * structured check results.
 *
 * Checks available:
 *   - trialBalanceCheck()       – verifies total GL debits == total GL credits.
 *   - incomeTotalsCheck()       – returns income totals by account for the period.
 *   - cashPositionCheck()       – returns cash/bank/mobile-money balances as-of a date.
 *   - loanRepaymentCrossCheck() – compares loan repayment principal totals vs GL
 *                                  postings to the Loans Receivable account (1100),
 *                                  with configurable tolerance.
 *
 * Each check method returns an array shaped:
 *   [ 'passed' => bool, 'details' => array, 'message' => string ]
 */
class GlBaselineCheckService
{
    /**
     * GL account codes for cash-position accounts.
     * Driven by config so they can be overridden per deployment.
     */
    protected function cashAccountCodes(): array
    {
        return array_values(config('sacco.payment_method_gl_accounts', [
            'cash'          => '1001',
            'bank_transfer' => '1002',
            'mobile_money'  => '1003',
        ]));
    }

    /**
     * Verify that the GL is in balance (total debits == total credits) for all
     * posted entries up to the given date.
     *
     * @param  string|null  $asOfDate  YYYY-MM-DD; defaults to today.
     * @return array{passed: bool, details: array, message: string}
     */
    public function trialBalanceCheck(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now()->toDateString();

        $row = GeneralLedger::where('status', 'posted')
            ->whereDate('transaction_date', '<=', $asOfDate)
            ->selectRaw('SUM(debit_amount) as total_debits, SUM(credit_amount) as total_credits')
            ->first();

        $totalDebits  = sprintf('%.2f', (float) ($row->total_debits  ?? 0));
        $totalCredits = sprintf('%.2f', (float) ($row->total_credits ?? 0));
        $difference   = bcsub($totalDebits, $totalCredits, 2);
        $passed       = bccomp($totalDebits, $totalCredits, 2) === 0;

        $details = [
            'as_of_date'    => $asOfDate,
            'total_debits'  => $totalDebits,
            'total_credits' => $totalCredits,
            'difference'    => $difference,
        ];

        if (!$passed) {
            Log::warning('[GlBaselineCheck] Trial balance is out of balance', $details);
        }

        return [
            'passed'  => $passed,
            'details' => $details,
            'message' => $passed
                ? "Trial balance is balanced as of {$asOfDate}."
                : "Trial balance out of balance as of {$asOfDate}: difference = {$difference}.",
        ];
    }

    /**
     * Return income account totals (net credit − debit) for the given period.
     * Does not assert a specific value; just returns verifiable totals for use
     * in report validation.
     *
     * @param  string|null  $startDate  YYYY-MM-DD; defaults to start of current month.
     * @param  string|null  $endDate    YYYY-MM-DD; defaults to today.
     * @return array{passed: bool, details: array, message: string}
     */
    public function incomeTotalsCheck(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth()->toDateString();
        $endDate   = $endDate   ?? now()->toDateString();

        // Use whereDate() so that datetime-stored values (e.g. '2026-04-17 00:00:00')
        // are compared by date part only, making the check DB-agnostic.
        $rows = GeneralLedger::where('status', 'posted')
            ->where('account_type', 'income')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->selectRaw('account_code, account_name, SUM(credit_amount) as credits, SUM(debit_amount) as debits')
            ->groupBy('account_code', 'account_name')
            ->get();

        $accounts    = [];
        $totalIncome = '0.00';

        foreach ($rows as $row) {
            $net          = bcsub((string) round((float) $row->credits, 2), (string) round((float) $row->debits, 2), 2);
            $accounts[]   = [
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'net_income'   => $net,
            ];
            $totalIncome = bcadd($totalIncome, $net, 2);
        }

        $details = [
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'accounts'     => $accounts,
            'total_income' => $totalIncome,
        ];

        return [
            'passed'  => true, // This check is informational only; always passes.
            'details' => $details,
            'message' => "Income totals for {$startDate}–{$endDate}: total = {$totalIncome}.",
        ];
    }

    /**
     * Return cash/bank/mobile-money position as-of a date, derived from GL
     * postings to the configured cash asset accounts.
     *
     * @param  string|null  $asOfDate  YYYY-MM-DD; defaults to today.
     * @return array{passed: bool, details: array, message: string}
     */
    public function cashPositionCheck(?string $asOfDate = null): array
    {
        $asOfDate   = $asOfDate ?? now()->toDateString();
        $cashCodes  = $this->cashAccountCodes();

        // Use whereDate() to compare by date part only (handles datetime storage).
        $rows = GeneralLedger::where('status', 'posted')
            ->whereIn('account_code', $cashCodes)
            ->whereDate('transaction_date', '<=', $asOfDate)
            ->selectRaw('account_code, account_name, SUM(debit_amount) as debits, SUM(credit_amount) as credits')
            ->groupBy('account_code', 'account_name')
            ->get();

        $accounts    = [];
        $totalCash   = '0.00';

        foreach ($rows as $row) {
            $balance     = bcsub((string) round((float) $row->debits, 2), (string) round((float) $row->credits, 2), 2);
            $accounts[]  = [
                'account_code' => $row->account_code,
                'account_name' => $row->account_name,
                'balance'      => $balance,
            ];
            $totalCash = bcadd($totalCash, $balance, 2);
        }

        $details = [
            'as_of_date'   => $asOfDate,
            'accounts'     => $accounts,
            'total_cash'   => $totalCash,
        ];

        return [
            'passed'  => true, // Informational; always passes.
            'details' => $details,
            'message' => "Cash position as of {$asOfDate}: total = {$totalCash}.",
        ];
    }

    /**
     * Cross-check loan repayment principal totals against GL postings to the
     * Loans Receivable account (1100).
     *
     * The check flags a discrepancy if the absolute difference exceeds $tolerance.
     *
     * @param  string|null  $startDate    YYYY-MM-DD; defaults to start of current month.
     * @param  string|null  $endDate      YYYY-MM-DD; defaults to today.
     * @param  float        $tolerance    Max acceptable difference (default 0.01).
     * @return array{passed: bool, details: array, message: string}
     */
    public function loanRepaymentCrossCheck(
        ?string $startDate = null,
        ?string $endDate   = null,
        float   $tolerance = 0.01
    ): array {
        $startDate = $startDate ?? now()->startOfMonth()->toDateString();
        $endDate   = $endDate   ?? now()->toDateString();

        // Total principal from loan_repayments table.
        $repaymentPrincipal = sprintf('%.2f', (float) LoanRepayment::whereDate('payment_date', '>=', $startDate)
                ->whereDate('payment_date', '<=', $endDate)
                ->whereIn('status', ['paid', 'partial'])
                ->sum('principal_amount'));

        // Total credits to Loans Receivable (1100) from GL.
        // Each principal repayment is posted as a credit to 1100.
        $glLoansReceivableCredits = sprintf('%.2f', (float) GeneralLedger::where('status', 'posted')
                ->where('account_code', '1100')
                ->whereDate('transaction_date', '>=', $startDate)
                ->whereDate('transaction_date', '<=', $endDate)
                ->sum('credit_amount'));

        $difference = bcsub($repaymentPrincipal, $glLoansReceivableCredits, 2);
        $absGap     = abs((float) $difference);
        $passed     = $absGap <= $tolerance;

        $details = [
            'start_date'                  => $startDate,
            'end_date'                    => $endDate,
            'repayment_principal_total'   => $repaymentPrincipal,
            'gl_loans_receivable_credits' => $glLoansReceivableCredits,
            'difference'                  => $difference,
            'tolerance'                   => $tolerance,
        ];

        if (!$passed) {
            Log::warning('[GlBaselineCheck] Loan repayment cross-check failed', $details);
        }

        return [
            'passed'  => $passed,
            'details' => $details,
            'message' => $passed
                ? "Loan repayment cross-check passed for {$startDate}–{$endDate}."
                : "Loan repayment cross-check FAILED for {$startDate}–{$endDate}: difference = {$difference} (tolerance {$tolerance}).",
        ];
    }
}
