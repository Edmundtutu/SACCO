<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\GeneralLedger;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\LoanRepayment;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\GlBaselineCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 1 PR 4 — Reporting Baseline Validation Utility Tests
 *
 * Verifies:
 * 1. trialBalanceCheck() passes for a balanced ledger.
 * 2. trialBalanceCheck() detects an out-of-balance ledger.
 * 3. incomeTotalsCheck() returns correct income totals.
 * 4. cashPositionCheck() returns correct cash position.
 * 5. loanRepaymentCrossCheck() passes when GL credits == repayment principals.
 * 6. loanRepaymentCrossCheck() detects a discrepancy (within and beyond tolerance).
 * 7. All checks are read-only (no data modifications).
 * 8. Regression: existing report-related API endpoints continue to function.
 */
class GlBaselineCheckTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $member;
    protected User $staff;
    protected Loan $loan;
    protected LoanAccount $loanAccount;
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'sacco_code'        => 'BL01',
            'sacco_name'        => 'Baseline SACCO',
            'slug'              => 'baseline-sacco',
            'status'            => 'active',
            'subscription_plan' => 'basic',
        ]);

        setTenant($this->tenant);

        $this->member = User::factory()->create([
            'status'    => 'active',
            'role'      => 'member',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->staff = User::factory()->create([
            'status'    => 'active',
            'role'      => 'staff_level_1',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->loanAccount = LoanAccount::factory()->fresh()->create([
            'min_loan_limit' => 1000,
            'max_loan_limit' => 1000000,
        ]);

        $this->account = Account::create([
            'member_id'        => $this->member->id,
            'accountable_type' => LoanAccount::class,
            'accountable_id'   => $this->loanAccount->id,
            'account_number'   => 'LA' . str_pad($this->loanAccount->id, 8, '0', STR_PAD_LEFT),
            'status'           => 'active',
        ]);

        $this->loan = Loan::factory()->disbursed()->create([
            'member_id'           => $this->member->id,
            'loan_account_id'     => $this->loanAccount->id,
            'principal_amount'    => 50000,
            'outstanding_balance' => 50000,
            'principal_balance'   => 50000,
            'interest_balance'    => 5000,
            'penalty_balance'     => 0,
            'total_paid'          => 0,
            'status'              => 'active',
        ]);

        config(['sacco.minimum_repayment_amount' => 1000]);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    protected function makeRepayment(float $amount = 5000): void
    {
        $this->actingAs($this->member, 'api');
        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => $amount,
            'payment_method' => 'cash',
        ])->assertStatus(200);
    }

    // =========================================================================
    // 1. trialBalanceCheck — balanced ledger
    // =========================================================================

    public function test_trial_balance_check_passes_for_empty_ledger(): void
    {
        $service = new GlBaselineCheckService();
        $result  = $service->trialBalanceCheck();

        $this->assertTrue($result['passed']);
        $this->assertEquals('0.00', $result['details']['total_debits']);
        $this->assertEquals('0.00', $result['details']['total_credits']);
    }

    public function test_trial_balance_check_passes_after_real_repayment(): void
    {
        $this->makeRepayment(5000);

        $service = new GlBaselineCheckService();
        $result  = $service->trialBalanceCheck();

        $this->assertTrue($result['passed'], $result['message']);
        $this->assertEquals('0.00', $result['details']['difference']);
    }

    // =========================================================================
    // 2. trialBalanceCheck — out-of-balance detection
    // =========================================================================

    public function test_trial_balance_check_detects_imbalanced_ledger(): void
    {
        // Inject a lone debit with no matching credit.
        GeneralLedger::create([
            'transaction_id'   => 'GL-TEST-IMBAL',
            'transaction_date' => now()->toDateString(),
            'account_code'     => '1001',
            'account_name'     => 'Cash in Hand',
            'account_type'     => 'asset',
            'debit_amount'     => 1000,
            'credit_amount'    => 0,
            'description'      => 'Imbalanced test entry',
            'reference_type'   => 'Test',
            'reference_id'     => 1,
            'member_id'        => $this->member->id,
            'batch_id'         => 'TEST-BATCH',
            'status'           => 'posted',
            'posted_by'        => $this->staff->id,
            'posted_at'        => now(),
        ]);

        $service = new GlBaselineCheckService();
        $result  = $service->trialBalanceCheck();

        $this->assertFalse($result['passed']);
        $this->assertEquals('1000.00', $result['details']['difference']);
    }

    // =========================================================================
    // 3. incomeTotalsCheck
    // =========================================================================

    public function test_income_totals_check_returns_correct_totals_after_repayment(): void
    {
        // Repayment with interest — should create interest income GL entry.
        $this->makeRepayment(5000);

        $service = new GlBaselineCheckService();
        $result  = $service->incomeTotalsCheck();

        $this->assertTrue($result['passed']);

        // Interest income (account 4001) should appear in the income accounts.
        $incomeCodes = array_column($result['details']['accounts'], 'account_code');
        $this->assertContains('4001', $incomeCodes, 'Interest income account 4001 should appear');

        // Total income should be greater than zero.
        $this->assertGreaterThan(0, (float) $result['details']['total_income']);
    }

    public function test_income_totals_check_returns_empty_for_empty_ledger(): void
    {
        $service = new GlBaselineCheckService();
        $result  = $service->incomeTotalsCheck();

        $this->assertTrue($result['passed']);
        $this->assertEmpty($result['details']['accounts']);
        $this->assertEquals('0.00', $result['details']['total_income']);
    }

    // =========================================================================
    // 4. cashPositionCheck
    // =========================================================================

    public function test_cash_position_check_returns_correct_cash_after_repayment(): void
    {
        $this->makeRepayment(5000);

        $service = new GlBaselineCheckService();
        $result  = $service->cashPositionCheck();

        $this->assertTrue($result['passed']);

        // Cash account 1001 should show a positive balance.
        $cashAccount = collect($result['details']['accounts'])
            ->firstWhere('account_code', '1001');

        $this->assertNotNull($cashAccount, 'Cash account 1001 should appear in cash position');
        $this->assertGreaterThan(0, (float) $cashAccount['balance']);

        // Total cash should be positive.
        $this->assertGreaterThan(0, (float) $result['details']['total_cash']);
    }

    public function test_cash_position_check_returns_zero_for_empty_ledger(): void
    {
        $service = new GlBaselineCheckService();
        $result  = $service->cashPositionCheck();

        $this->assertTrue($result['passed']);
        $this->assertEquals('0.00', $result['details']['total_cash']);
    }

    // =========================================================================
    // 5. loanRepaymentCrossCheck — passing case
    // =========================================================================

    public function test_loan_repayment_cross_check_passes_after_normal_repayment(): void
    {
        $this->makeRepayment(5000);

        $service = new GlBaselineCheckService();
        $result  = $service->loanRepaymentCrossCheck();

        $this->assertTrue($result['passed'], $result['message']);
    }

    public function test_loan_repayment_cross_check_passes_for_empty_data(): void
    {
        $service = new GlBaselineCheckService();
        $result  = $service->loanRepaymentCrossCheck();

        // 0 principal vs 0 GL credits → pass.
        $this->assertTrue($result['passed']);
        $this->assertEquals('0.00', $result['details']['repayment_principal_total']);
        $this->assertEquals('0.00', $result['details']['gl_loans_receivable_credits']);
    }

    // =========================================================================
    // 6. loanRepaymentCrossCheck — discrepancy detection
    // =========================================================================

    public function test_loan_repayment_cross_check_detects_discrepancy_beyond_tolerance(): void
    {
        $this->makeRepayment(5000);

        // Create a spurious LoanRepayment record that is NOT reflected in GL.
        $loan = $this->loan->fresh();
        LoanRepayment::create([
            'loan_id'              => $loan->id,
            'receipt_number'       => 'RCP-GHOST-001',
            'installment_number'   => 99,
            'due_date'             => now()->toDateString(),
            'payment_date'         => now()->toDateString(),
            'scheduled_amount'     => 10000,
            'principal_amount'     => 10000, // unmatched in GL
            'interest_amount'      => 0,
            'penalty_amount'       => 0,
            'total_amount'         => 10000,
            'balance_after_payment'=> 0,
            'days_late'            => 0,
            'status'               => 'paid',
            'payment_method'       => 'cash',
        ]);

        $service = new GlBaselineCheckService();
        $result  = $service->loanRepaymentCrossCheck(tolerance: 0.01);

        $this->assertFalse($result['passed'], $result['message']);
        $this->assertGreaterThan(0.01, abs((float) $result['details']['difference']));
    }

    public function test_loan_repayment_cross_check_passes_within_tolerance(): void
    {
        $this->makeRepayment(5000);

        // Add a tiny amount to loan_repayments.principal_amount via direct DB
        // update to simulate a float rounding artefact below the tolerance.
        $repayment = LoanRepayment::first();
        if ($repayment) {
            $repayment->update(['principal_amount' => $repayment->principal_amount + 0.005]);
        }

        $service = new GlBaselineCheckService();
        $result  = $service->loanRepaymentCrossCheck(tolerance: 0.01);

        $this->assertTrue($result['passed'], $result['message']);
    }

    // =========================================================================
    // 7. All checks are read-only
    // =========================================================================

    public function test_all_checks_do_not_modify_gl_data(): void
    {
        $this->makeRepayment(5000);

        $glCountBefore = GeneralLedger::count();

        $service = new GlBaselineCheckService();
        $service->trialBalanceCheck();
        $service->incomeTotalsCheck();
        $service->cashPositionCheck();
        $service->loanRepaymentCrossCheck();

        $glCountAfter = GeneralLedger::count();

        $this->assertEquals($glCountBefore, $glCountAfter, 'GL row count must be unchanged after baseline checks');
    }

    // =========================================================================
    // 8. Regression: existing report-related logic still works
    // =========================================================================

    public function test_ledger_service_trial_balance_still_functions(): void
    {
        $this->makeRepayment(5000);

        // The existing LedgerService::getTrialBalance() must still work.
        $ledgerService = app(\App\Services\LedgerService::class);
        $trialBalance  = $ledgerService->getTrialBalance();

        $this->assertArrayHasKey('as_of_date', $trialBalance);
        $this->assertArrayHasKey('accounts', $trialBalance);
        $this->assertArrayHasKey('total_debits', $trialBalance);
        $this->assertArrayHasKey('total_credits', $trialBalance);
        $this->assertArrayHasKey('is_balanced', $trialBalance);

        $this->assertTrue($trialBalance['is_balanced']);
        $this->assertNotEmpty($trialBalance['accounts']);
    }

    public function test_cash_position_check_filters_correctly_by_date(): void
    {
        // Make a repayment.
        $this->makeRepayment(5000);

        // Cash position as-of yesterday should be zero (nothing posted then).
        $service = new GlBaselineCheckService();
        $result  = $service->cashPositionCheck(now()->subDay()->toDateString());

        $this->assertEquals('0.00', $result['details']['total_cash']);
    }

    public function test_income_totals_check_respects_date_range(): void
    {
        // Make a repayment today.
        $this->makeRepayment(5000);

        // Query for yesterday only — should return zero income.
        $yesterday = now()->subDay()->toDateString();
        $service   = new GlBaselineCheckService();
        $result    = $service->incomeTotalsCheck($yesterday, $yesterday);

        $this->assertEquals('0.00', $result['details']['total_income']);
    }
}
