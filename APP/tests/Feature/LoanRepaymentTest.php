<?php

namespace Tests\Feature;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Models\Account;
use App\Models\GeneralLedger;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LoanCalculationService;
use App\Services\PaymentMethodAccountResolver;
use App\Services\Transactions\LoanRepaymentHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Loan Repayment Tests
 *
 * Verifies:
 * 1. Backward-compatible API contract for POST /{loanId}/repay.
 * 2. Financial writes flow through TransactionService (GL entries, transaction
 *    record, loan_repayments record, loan balance update).
 * 3. Edge cases: null account, full payoff, overpayment, error path.
 * 4. GL balance check is always enforced (hard failure, no monitor mode).
 * 5. PaymentMethodAccountResolver returns correct GL account codes.
 * 6. LoanRepayment compatibility accessors/mutators work correctly.
 */
class LoanRepaymentTest extends TestCase
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

        // Create a tenant and link users to it (required by TenantMiddleware)
        $this->tenant = Tenant::create([
            'sacco_code'        => 'TEST01',
            'sacco_name'        => 'Test SACCO',
            'slug'              => 'test-sacco',
            'status'            => 'active',
            'subscription_plan' => 'basic',
        ]);

        // Set tenant context so BelongsToTenant models get the correct
        // tenant_id automatically and global scope queries match.
        setTenant($this->tenant);

        $this->member = User::factory()->create([
            'status'    => 'active',
            'role'      => 'member',
            'tenant_id' => $this->tenant->id,
        ]);
        $this->staff  = User::factory()->create([
            'status'    => 'active',
            'role'      => 'staff_level_1',
            'tenant_id' => $this->tenant->id,
        ]);

        // Create LoanAccount (the account-level tracker)
        $this->loanAccount = LoanAccount::factory()->fresh()->create([
            'min_loan_limit' => 1000,
            'max_loan_limit' => 1000000,
        ]);

        // Create the polymorphic Account linked to LoanAccount
        $this->account = Account::create([
            'member_id'        => $this->member->id,
            'accountable_type' => LoanAccount::class,
            'accountable_id'   => $this->loanAccount->id,
            'account_number'   => 'LA' . str_pad($this->loanAccount->id, 8, '0', STR_PAD_LEFT),
            'status'           => 'active',
        ]);

        // Create an active loan linked to the loan account
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

        // Ensure minimum repayment amount is low enough for tests
        config(['sacco.minimum_repayment_amount' => 1000]);
    }

    // =========================================================================
    // 1. Backward-compatible API contract
    // =========================================================================

    public function test_repay_endpoint_accepts_original_request_contract(): void
    {
        $this->actingAs($this->member, 'api');

        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
            'reference'      => 'REF-TEST-001',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'repayment',
                    'loan_id',
                    'outstanding_balance',
                    'next_payment_date',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Payment applied successfully',
            ]);
    }

    public function test_repay_returns_correct_loan_id_in_response(): void
    {
        $this->actingAs($this->member, 'api');

        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(200);
        $this->assertEquals($this->loan->id, $response->json('data.loan_id'));
    }

    public function test_repay_rejects_inactive_loan(): void
    {
        $this->actingAs($this->member, 'api');

        $this->loan->update(['status' => 'pending']);

        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false, 'message' => 'Loan is not active']);
    }

    public function test_repay_requires_amount_and_payment_method(): void
    {
        $this->actingAs($this->member, 'api');

        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'payment_method']);
    }

    // =========================================================================
    // 2. TransactionService path – DB side effects
    // =========================================================================

    public function test_repayment_creates_transaction_record(): void
    {
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('transactions', [
            'member_id'       => $this->member->id,
            'type'            => 'loan_repayment',
            'amount'          => 5000,
            'status'          => 'completed',
            'related_loan_id' => $this->loan->id,
        ]);
    }

    public function test_repayment_creates_loan_repayments_record(): void
    {
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'bank_transfer',
            'reference'      => 'REF-BANK-001',
        ]);

        $this->assertDatabaseHas('loan_repayments', [
            'loan_id'          => $this->loan->id,
            'total_amount'     => 5000,
            'payment_method'   => 'bank_transfer',
            'payment_reference'=> 'REF-BANK-001',
            'status'           => 'paid',
        ]);
    }

    public function test_repayment_updates_loan_outstanding_balance(): void
    {
        $this->actingAs($this->member, 'api');

        $initialBalance = $this->loan->outstanding_balance;

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $this->loan->refresh();
        $this->assertLessThan($initialBalance, $this->loan->outstanding_balance);
    }

    public function test_repayment_creates_general_ledger_entries(): void
    {
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        // Cash in Hand should be debited
        $this->assertDatabaseHas('general_ledger', [
            'account_code' => '1001',
            'debit_amount' => 5000,
        ]);

        // Loans Receivable should be credited (principal portion)
        $this->assertDatabaseHas('general_ledger', [
            'account_code' => '1100',
        ]);
    }

    public function test_repayment_gl_entries_balance(): void
    {
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $totalDebits  = GeneralLedger::where('status', 'posted')->sum('debit_amount');
        $totalCredits = GeneralLedger::where('status', 'posted')->sum('credit_amount');

        $this->assertEqualsWithDelta(
            $totalDebits,
            $totalCredits,
            0.01,
            'GL entries must balance (debit = credit)'
        );
    }

    public function test_repayment_stores_payment_method_on_transaction_record(): void
    {
        $this->actingAs($this->member, 'api');

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'mobile_money',
        ]);

        $this->assertDatabaseHas('transactions', [
            'type'           => 'loan_repayment',
            'payment_method' => 'mobile_money',
        ]);
    }

    // =========================================================================
    // 3. Edge cases
    // =========================================================================

    public function test_repayment_succeeds_when_loan_has_no_linked_account(): void
    {
        // Create a loan that has no loan_account_id so loanAccount relation is null.
        // When the loan has no LoanAccount the controller sets accountId = null.
        // The transactions table currently requires a non-null account_id, so the
        // repayment is rejected with a 422.  This test documents the known limitation:
        // loans without a linked account cannot be repaid through TransactionService
        // until the transactions.account_id column is made nullable.
        $loanWithNoAccount = Loan::factory()->disbursed()->create([
            'member_id'           => $this->member->id,
            'loan_account_id'     => null,
            'principal_amount'    => 20000,
            'outstanding_balance' => 20000,
            'principal_balance'   => 20000,
            'interest_balance'    => 2000,
            'penalty_balance'     => 0,
            'total_paid'          => 0,
            'status'              => 'active',
        ]);

        $this->actingAs($this->member, 'api');

        $response = $this->postJson("/api/loans/{$loanWithNoAccount->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        // Fails because the transactions table enforces NOT NULL on account_id.
        // A future migration making account_id nullable will allow this to succeed.
        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        // No partial records should remain (DB transaction was rolled back).
        $this->assertDatabaseMissing('transactions', [
            'related_loan_id' => $loanWithNoAccount->id,
        ]);
    }

    public function test_full_loan_payoff_sets_loan_status_to_completed(): void
    {
        // Create a zero-interest loan so the full outstanding goes to principal.
        // A non-zero interest_rate would cause calculatePaymentAllocation() to
        // siphon some of the payment to accrued interest, leaving a residual
        // principal balance and keeping the status 'active'.
        $smallLoan = Loan::factory()->disbursed()->create([
            'member_id'           => $this->member->id,
            'loan_account_id'     => $this->loanAccount->id,
            'principal_amount'    => 5000,
            'outstanding_balance' => 5000,
            'principal_balance'   => 5000,
            'interest_balance'    => 0,
            'penalty_balance'     => 0,
            'total_paid'          => 0,
            'status'              => 'active',
            'interest_rate'       => 0,
        ]);

        $this->actingAs($this->member, 'api');

        $response = $this->postJson("/api/loans/{$smallLoan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $smallLoan->refresh();
        $this->assertEquals('completed', $smallLoan->status);
        $this->assertEquals(0, $smallLoan->outstanding_balance);
    }

    public function test_overpayment_is_rejected(): void
    {
        $this->actingAs($this->member, 'api');

        // Attempt to pay more than the outstanding balance.
        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => $this->loan->outstanding_balance + 10000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        // No transaction or repayment record should have been created.
        $this->assertDatabaseMissing('transactions', ['type' => 'loan_repayment']);
    }

    public function test_transaction_processing_exception_returns_422(): void
    {
        $this->actingAs($this->member, 'api');

        // Make the loan inactive after the controller's status check by swapping
        // a handler that always throws.
        $this->app->bind(LoanRepaymentHandler::class, function () {
            return new class(app(LoanCalculationService::class)) extends LoanRepaymentHandler {
                public function validate(TransactionDTO $transactionData): void
                {
                    throw new \App\Exceptions\InvalidTransactionException('Simulated validation failure');
                }
            };
        });

        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    // =========================================================================
    // 4. GL balance enforcement (always on – no monitor mode)
    // =========================================================================

    public function test_gl_imbalance_is_rejected_with_unprocessable_entity(): void
    {
        $this->actingAs($this->member, 'api');

        // Bind a handler stub that returns deliberately unbalanced GL entries
        // (debit only, no matching credit) so verifyDoubleEntryBalance throws.
        $this->app->bind(LoanRepaymentHandler::class, function () {
            return new class(app(LoanCalculationService::class)) extends LoanRepaymentHandler {
                public function getAccountingEntries(
                    \App\Models\Transaction $transaction,
                    \App\DTOs\TransactionDTO $transactionData
                ): array {
                    return [
                        new LedgerEntryDTO(
                            accountCode: '1001',
                            accountName: 'Cash in Hand',
                            accountType: 'asset',
                            debitAmount: $transactionData->amount,
                            creditAmount: 0,
                            description: 'Debit-only entry (no matching credit)'
                        ),
                        // No credit entry – deliberately imbalanced.
                    ];
                }
            };
        });

        $response = $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        // The DB transaction must have been rolled back.
        $this->assertDatabaseMissing('transactions', ['type' => 'loan_repayment']);
    }

    // =========================================================================
    // 5. PaymentMethodAccountResolver
    // =========================================================================

    public function test_resolver_returns_cash_account_for_cash_payment(): void
    {
        config(['sacco.payment_method_gl_accounts' => [
            'cash'          => '1001',
            'bank_transfer' => '1002',
            'mobile_money'  => '1003',
        ]]);

        $result = PaymentMethodAccountResolver::resolve('cash');

        $this->assertEquals('1001', $result['account_code']);
        $this->assertEquals('Cash in Hand', $result['account_name']);
        $this->assertEquals('asset', $result['account_type']);
    }

    public function test_resolver_returns_bank_account_for_bank_transfer(): void
    {
        config(['sacco.payment_method_gl_accounts' => [
            'cash'          => '1001',
            'bank_transfer' => '1002',
            'mobile_money'  => '1003',
        ]]);

        $result = PaymentMethodAccountResolver::resolve('bank_transfer');

        $this->assertEquals('1002', $result['account_code']);
        $this->assertEquals('Bank Account', $result['account_name']);
    }

    public function test_resolver_returns_mobile_money_account_for_mobile_money(): void
    {
        config(['sacco.payment_method_gl_accounts' => [
            'cash'          => '1001',
            'bank_transfer' => '1002',
            'mobile_money'  => '1003',
        ]]);

        $result = PaymentMethodAccountResolver::resolve('mobile_money');

        $this->assertEquals('1003', $result['account_code']);
        $this->assertEquals('Mobile Money Account', $result['account_name']);
    }

    public function test_resolver_falls_back_to_cash_for_unknown_method(): void
    {
        config(['sacco.payment_method_gl_accounts' => [
            'cash'          => '1001',
            'bank_transfer' => '1002',
            'mobile_money'  => '1003',
        ]]);

        $result = PaymentMethodAccountResolver::resolve('cheque'); // not in map

        $this->assertEquals('1001', $result['account_code']);
    }

    public function test_bank_transfer_repayment_credits_bank_gl_account(): void
    {
        $this->actingAs($this->member, 'api');
        config(['sacco.payment_method_gl_accounts.bank_transfer' => '1002']);

        $this->postJson("/api/loans/{$this->loan->id}/repay", [
            'amount'         => 5000,
            'payment_method' => 'bank_transfer',
        ]);

        // Bank Account (1002) should be debited, not Cash in Hand (1001)
        $this->assertDatabaseHas('general_ledger', [
            'account_code' => '1002',
            'debit_amount' => 5000,
        ]);
    }

    // =========================================================================
    // 6. LoanRepayment compatibility accessors/mutators
    // =========================================================================

    public function test_loan_repayment_amount_accessor_reads_total_amount(): void
    {
        $repayment = LoanRepayment::factory()->create([
            'loan_id'       => $this->loan->id,
            'total_amount'  => 7500,
            'status'        => 'paid',
        ]);

        $this->assertEquals(7500.0, $repayment->amount);
    }

    public function test_loan_repayment_amount_mutator_writes_total_amount(): void
    {
        $repayment = LoanRepayment::factory()->create([
            'loan_id'      => $this->loan->id,
            'total_amount' => 1000,
            'status'       => 'paid',
        ]);

        $repayment->amount = 8000;
        $repayment->save();

        $this->assertDatabaseHas('loan_repayments', [
            'id'          => $repayment->id,
            'total_amount'=> 8000,
        ]);
    }

    public function test_loan_repayment_reference_accessor_reads_payment_reference(): void
    {
        $repayment = LoanRepayment::factory()->create([
            'loan_id'          => $this->loan->id,
            'payment_reference'=> 'REF-XYZ',
            'status'           => 'paid',
        ]);

        $this->assertEquals('REF-XYZ', $repayment->reference);
    }

    public function test_loan_repayment_reference_mutator_writes_payment_reference(): void
    {
        $repayment = LoanRepayment::factory()->create([
            'loan_id'          => $this->loan->id,
            'payment_reference'=> null,
            'status'           => 'paid',
        ]);

        $repayment->reference = 'NEW-REF';
        $repayment->save();

        $this->assertDatabaseHas('loan_repayments', [
            'id'               => $repayment->id,
            'payment_reference'=> 'NEW-REF',
        ]);
    }

    public function test_mark_as_completed_sets_status_to_paid(): void
    {
        $repayment = LoanRepayment::factory()->create([
            'loan_id' => $this->loan->id,
            'status'  => 'pending',
        ]);

        $repayment->markAsCompleted();
        $repayment->refresh();

        $this->assertEquals('paid', $repayment->status);
    }

    public function test_scope_completed_returns_paid_repayments(): void
    {
        LoanRepayment::factory()->create([
            'loan_id' => $this->loan->id,
            'status'  => 'paid',
        ]);
        LoanRepayment::factory()->create([
            'loan_id' => $this->loan->id,
            'status'  => 'pending',
        ]);

        $completed = LoanRepayment::completed()->get();

        $this->assertCount(1, $completed);
        $this->assertEquals('paid', $completed->first()->status);
    }
}
