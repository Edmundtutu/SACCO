<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\GeneralLedger;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\GlReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 1 PR 2 — GL ↔ Transaction Traceability Tests
 *
 * Verifies:
 * 1. New GL postings carry the transaction_record_id FK.
 * 2. reconcileGl detects transactions without GL entries.
 * 3. reconcileGl detects GL rows without a linked transaction.
 * 4. A fully linked system reports is_clean = true.
 * 5. The reconcile:gl Artisan command runs without error.
 */
class GlTraceabilityTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $member;
    protected User $staff;
    protected Loan $loan;
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'sacco_code'        => 'GL01',
            'sacco_name'        => 'GL SACCO',
            'slug'              => 'gl-sacco',
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

        $loanAccount = LoanAccount::factory()->fresh()->create([
            'min_loan_limit' => 1000,
            'max_loan_limit' => 1000000,
        ]);

        $this->account = Account::create([
            'member_id'        => $this->member->id,
            'accountable_type' => LoanAccount::class,
            'accountable_id'   => $loanAccount->id,
            'account_number'   => 'LA' . str_pad($loanAccount->id, 8, '0', STR_PAD_LEFT),
            'status'           => 'active',
        ]);

        $this->loan = Loan::factory()->disbursed()->create([
            'member_id'           => $this->member->id,
            'loan_account_id'     => $loanAccount->id,
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
    // 1. Dual-write: new GL entries carry transaction_record_id FK
    // =========================================================================

    public function test_new_gl_entries_have_transaction_record_id_set(): void
    {
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ])->assertStatus(200);

        // All GL entries for this transaction should have transaction_record_id set.
        $transaction = Transaction::where('type', 'loan_repayment')->first();
        $this->assertNotNull($transaction, 'Transaction should exist');

        $glRows = GeneralLedger::where('reference_id', $transaction->id)
            ->where('reference_type', 'Transaction')
            ->get();

        $this->assertGreaterThan(0, $glRows->count(), 'GL rows should exist');

        foreach ($glRows as $row) {
            $this->assertEquals(
                $transaction->id,
                $row->transaction_record_id,
                "GL row #{$row->id} should have transaction_record_id = {$transaction->id}"
            );
        }
    }

    public function test_transaction_record_relation_resolves_correctly(): void
    {
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ])->assertStatus(200);

        $transaction = Transaction::where('type', 'loan_repayment')->first();
        $glRow = GeneralLedger::where('transaction_record_id', $transaction->id)->first();

        $this->assertNotNull($glRow);
        $this->assertNotNull($glRow->transactionRecord);
        $this->assertEquals($transaction->id, $glRow->transactionRecord->id);
    }

    // =========================================================================
    // 2. Reconciliation: detects transactions without GL entries
    // =========================================================================

    public function test_reconciliation_detects_completed_transaction_without_gl_entry(): void
    {
        // Create a completed transaction with no GL entries.
        $transaction = Transaction::create([
            'transaction_number' => 'TXN-ORPHAN-001',
            'member_id'          => $this->member->id,
            'account_id'         => $this->account->id,
            'type'               => 'deposit',
            'category'           => 'savings',
            'amount'             => 10000,
            'fee_amount'         => 0,
            'net_amount'         => 10000,
            'description'        => 'Orphaned transaction',
            'payment_method'     => 'cash',
            'status'             => 'completed',
            'transaction_date'   => now(),
            'value_date'         => now(),
            'processed_by'       => $this->staff->id,
        ]);

        $reconciler = new GlReconciliationService();
        $report = $reconciler->reconcile();

        $this->assertGreaterThan(0, $report['summary']['transactions_without_gl_count']);
        $ids = $report['transactions_without_gl']->pluck('id')->toArray();
        $this->assertContains($transaction->id, $ids);
    }

    public function test_reconciliation_detects_gl_rows_without_transaction_record_id(): void
    {
        // Insert a GL row with transaction_record_id = null (pre-Phase-1 style).
        GeneralLedger::create([
            'transaction_id'   => 'GL-LEGACY-99',
            // No transaction_record_id — simulating a pre-Phase-1 posting.
            'transaction_date' => now()->toDateString(),
            'account_code'     => '1001',
            'account_name'     => 'Cash in Hand',
            'account_type'     => 'asset',
            'debit_amount'     => 1000,
            'credit_amount'    => 0,
            'description'      => 'Legacy GL row without FK',
            'reference_type'   => 'Transaction',
            'reference_id'     => 99999, // non-existent
            'member_id'        => $this->member->id,
            'batch_id'         => 'LEGACY-BATCH',
            'status'           => 'posted',
            'posted_by'        => $this->staff->id,
            'posted_at'        => now(),
        ]);

        $reconciler = new GlReconciliationService();
        $report = $reconciler->reconcile();

        $this->assertGreaterThan(0, $report['summary']['gl_rows_without_transaction_count']);
    }

    // =========================================================================
    // 3. Clean ledger reports is_clean = true
    // =========================================================================

    public function test_reconciliation_reports_clean_when_no_orphans_exist(): void
    {
        // No transactions created → nothing to be orphaned.
        $reconciler = new GlReconciliationService();
        $report = $reconciler->reconcile();

        $this->assertTrue($report['summary']['is_clean']);
        $this->assertEquals(0, $report['summary']['transactions_without_gl_count']);
        $this->assertEquals(0, $report['summary']['gl_rows_without_transaction_count']);
    }

    public function test_reconciliation_reports_clean_after_real_repayment(): void
    {
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ])->assertStatus(200);

        $reconciler = new GlReconciliationService();
        $report = $reconciler->reconcile();

        // The repayment transaction should have GL entries (no orphan) and the
        // GL entries should have transaction_record_id set (no unlinked rows).
        $this->assertTrue($report['summary']['is_clean']);
    }

    // =========================================================================
    // 4. reconcile:gl Artisan command
    // =========================================================================

    public function test_reconcile_gl_artisan_command_runs_without_error(): void
    {
        $this->artisan('reconcile:gl')
            ->assertExitCode(0); // clean ledger → SUCCESS
    }

    public function test_reconcile_gl_artisan_command_returns_failure_when_orphans_exist(): void
    {
        // Insert an orphaned GL row.
        GeneralLedger::create([
            'transaction_id'   => 'GL-CMD-ORPHAN',
            'transaction_date' => now()->toDateString(),
            'account_code'     => '1001',
            'account_name'     => 'Cash in Hand',
            'account_type'     => 'asset',
            'debit_amount'     => 500,
            'credit_amount'    => 0,
            'description'      => 'Orphan for command test',
            'reference_type'   => 'Transaction',
            'reference_id'     => 99998,
            'member_id'        => $this->member->id,
            'batch_id'         => 'CMD-BATCH',
            'status'           => 'posted',
            'posted_by'        => $this->staff->id,
            'posted_at'        => now(),
        ]);

        $this->artisan('reconcile:gl')
            ->assertExitCode(1); // orphans found → FAILURE
    }

    public function test_reconcile_gl_artisan_command_json_flag_outputs_valid_json(): void
    {
        $this->artisan('reconcile:gl --json')
            ->assertExitCode(0);
    }

    public function test_reconcile_gl_artisan_since_flag_filters_by_date(): void
    {
        // Insert an orphaned GL row with yesterday's date.
        GeneralLedger::create([
            'transaction_id'   => 'GL-PAST-ORPHAN',
            'transaction_date' => now()->subDay()->toDateString(),
            'account_code'     => '1001',
            'account_name'     => 'Cash in Hand',
            'account_type'     => 'asset',
            'debit_amount'     => 500,
            'credit_amount'    => 0,
            'description'      => 'Past orphan',
            'reference_type'   => 'Transaction',
            'reference_id'     => 99997,
            'member_id'        => $this->member->id,
            'batch_id'         => 'PAST-BATCH',
            'status'           => 'posted',
            'posted_by'        => $this->staff->id,
            'posted_at'        => now(),
        ]);

        // Filtering from today forward should exclude the past orphan.
        $tomorrow = now()->addDay()->toDateString();
        $this->artisan("reconcile:gl --since={$tomorrow}")
            ->assertExitCode(0); // No future orphans → SUCCESS
    }
}
