<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\GeneralLedger;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\TransactionService;
use App\DTOs\TransactionDTO;
use App\Exceptions\TransactionProcessingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Phase 1 PR 3 — Double-entry Validation Hardening Tests
 *
 * Verifies:
 * 1. Precision-safe comparison uses bccomp (no float drift for sums that
 *    differ only in floating-point rounding).
 * 2. In 'monitor' mode an imbalance is logged but does NOT block the write.
 * 3. In 'enforce' mode an imbalance throws TransactionProcessingException.
 * 4. Balanced postings succeed in both modes.
 * 5. Feature flag can be toggled between monitor and enforce at runtime.
 */
class DoubleEntryValidationTest extends TestCase
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
            'sacco_code'        => 'DE01',
            'sacco_name'        => 'DoubleEntry SACCO',
            'slug'              => 'double-entry-sacco',
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
    // 1. Balanced postings succeed in both modes
    // =========================================================================

    public function test_balanced_repayment_succeeds_in_monitor_mode(): void
    {
        config(['sacco.double_entry_mode' => 'monitor']);

        $this->actingAs($this->member, 'api');

        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        // GL entries should exist and be balanced.
        $transaction = Transaction::where('type', 'loan_repayment')->first();
        $debits  = (float) GeneralLedger::where('transaction_record_id', $transaction->id)->sum('debit_amount');
        $credits = (float) GeneralLedger::where('transaction_record_id', $transaction->id)->sum('credit_amount');

        $this->assertEqualsWithDelta($debits, $credits, 0.01);
    }

    public function test_balanced_repayment_succeeds_in_enforce_mode(): void
    {
        config(['sacco.double_entry_mode' => 'enforce']);

        $this->actingAs($this->member, 'api');

        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // =========================================================================
    // 2. Monitor mode logs imbalance but does NOT block
    // =========================================================================

    public function test_monitor_mode_logs_imbalance_without_blocking(): void
    {
        config(['sacco.double_entry_mode' => 'monitor']);

        // First, create a valid repayment to get a transaction + GL entries.
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ])->assertStatus(200);

        $transaction = Transaction::where('type', 'loan_repayment')->first();

        // Corrupt the first GL debit entry by adding 1 to its debit_amount —
        // creating an artificial imbalance.
        $glRow = GeneralLedger::where('transaction_record_id', $transaction->id)
            ->where('debit_amount', '>', 0)
            ->first();
        $glRow->update(['debit_amount' => $glRow->debit_amount + 1]);

        // Use TransactionService reflection to call verifyDoubleEntryBalance
        // on the corrupted transaction.  This simulates monitor mode behaviour.
        $service = app(TransactionService::class);
        $method  = new \ReflectionMethod($service, 'verifyDoubleEntryBalance');
        $method->setAccessible(true);

        // In monitor mode this should NOT throw.
        $thrown = false;
        try {
            $method->invoke($service, $transaction);
        } catch (\Exception $e) {
            $thrown = true;
        }

        $this->assertFalse($thrown, 'Monitor mode must not throw on imbalance');
    }

    // =========================================================================
    // 3. Enforce mode blocks unbalanced postings
    // =========================================================================

    public function test_enforce_mode_blocks_unbalanced_postings(): void
    {
        config(['sacco.double_entry_mode' => 'enforce']);

        // Create a loan repayment transaction and GL entries (balanced).
        $this->actingAs($this->member, 'api');
        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ])->assertStatus(200);

        $transaction = Transaction::where('type', 'loan_repayment')->first();

        // Corrupt a GL entry to create an imbalance.
        $glRow = GeneralLedger::where('transaction_record_id', $transaction->id)
            ->where('debit_amount', '>', 0)
            ->first();
        $glRow->update(['debit_amount' => $glRow->debit_amount + 1]);

        // In enforce mode verifyDoubleEntryBalance MUST throw.
        $service = app(TransactionService::class);
        $method  = new \ReflectionMethod($service, 'verifyDoubleEntryBalance');
        $method->setAccessible(true);

        $this->expectException(TransactionProcessingException::class);
        $method->invoke($service, $transaction);
    }

    // =========================================================================
    // 4. Precision-safe comparison (bccomp)
    // =========================================================================

    public function test_precision_safe_comparison_treats_small_float_differences_as_equal(): void
    {
        config(['sacco.double_entry_mode' => 'enforce']);

        // Create a transaction with a GL debit/credit that differ only in the
        // 15th decimal place due to floating-point representation.  The
        // bccomp(2dp) check should treat these as equal.
        $this->actingAs($this->member, 'api');
        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ])->assertStatus(200);

        $transaction = Transaction::where('type', 'loan_repayment')->first();

        // Add floating-point noise below 0.005 (rounds to 0.00 at 2dp).
        $glRow = GeneralLedger::where('transaction_record_id', $transaction->id)
            ->where('debit_amount', '>', 0)
            ->first();
        $glRow->update(['debit_amount' => (float) $glRow->debit_amount + 0.004]);

        // This must NOT throw (bccomp at 2dp rounds away the noise).
        $service = app(TransactionService::class);
        $method  = new \ReflectionMethod($service, 'verifyDoubleEntryBalance');
        $method->setAccessible(true);

        $thrown = false;
        try {
            $method->invoke($service, $transaction);
        } catch (\Exception $e) {
            $thrown = true;
        }

        $this->assertFalse($thrown, 'Float noise below 0.005 must not trigger enforcement');
    }

    // =========================================================================
    // 5. Feature flag runtime toggle
    // =========================================================================

    public function test_feature_flag_default_is_monitor_mode(): void
    {
        // config/sacco.php default is 'monitor'.
        $this->assertEquals('monitor', config('sacco.double_entry_mode'));
    }

    public function test_feature_flag_can_be_set_to_enforce(): void
    {
        config(['sacco.double_entry_mode' => 'enforce']);
        $this->assertEquals('enforce', config('sacco.double_entry_mode'));
    }

    // =========================================================================
    // 6. Imbalance log context contains required fields
    // =========================================================================

    public function test_monitor_mode_imbalance_log_includes_required_context(): void
    {
        config(['sacco.double_entry_mode' => 'monitor']);

        $this->actingAs($this->member, 'api');
        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ])->assertStatus(200);

        $transaction = Transaction::where('type', 'loan_repayment')->first();

        // Corrupt to trigger the monitor log.
        $glRow = GeneralLedger::where('transaction_record_id', $transaction->id)
            ->where('debit_amount', '>', 0)
            ->first();
        $glRow->update(['debit_amount' => $glRow->debit_amount + 10]);

        // Verify that the warning context contains the expected keys by using
        // a Log fake and asserting the structured context.
        Log::spy();

        $service = app(TransactionService::class);
        $method  = new \ReflectionMethod($service, 'verifyDoubleEntryBalance');
        $method->setAccessible(true);
        $method->invoke($service, $transaction);

        // Assert Log::warning was called with the expected context keys.
        Log::shouldHaveReceived('warning')
            ->once()
            ->with(
                \Mockery::on(fn ($msg) => str_contains($msg, '[DoubleEntry]')),
                \Mockery::on(function ($context) use ($transaction) {
                    return isset($context['transaction_id'])
                        && isset($context['debits'])
                        && isset($context['credits'])
                        && isset($context['difference'])
                        && isset($context['mode'])
                        && $context['mode'] === 'monitor'
                        && $context['transaction_id'] === $transaction->id;
                })
            );
    }
}
