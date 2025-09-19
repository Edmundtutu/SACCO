<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\SavingsProduct;
use App\Models\Transaction;
use App\Models\GeneralLedger;
use App\Services\TransactionService;
use App\Services\BalanceService;
use App\Services\LedgerService;
use App\Services\ValidationService;
use App\Services\NumberGenerationService;

class SavingsTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $member;
    protected Account $account;
    protected User $staff;
    protected SavingsProduct $savingsProduct;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test member
        $this->member = User::factory()->create(['status' => 'active', 'role' => 'member']);
        
        // Create savings product
        $this->savingsProduct = SavingsProduct::factory()->create([
            'minimum_balance' => 5000,
            'withdrawal_fee' => 1000,
            'allow_partial_withdrawals' => true,
            'is_active' => true,
        ]);
        
        // Create member account
        $this->account = Account::factory()->create([
            'member_id' => $this->member->id,
            'savings_product_id' => $this->savingsProduct->id,
            'balance' => 50000,
            'available_balance' => 50000,
            'status' => 'active',
        ]);
        
        // Create staff user
        $this->staff = User::factory()->create(['role' => 'staff_level_1', 'status' => 'active']);
    }

    public function test_can_process_deposit_transaction(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        $depositData = [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 25000,
            'description' => 'Test deposit',
        ];

        $response = $this->postJson('/api/savings/deposit', $depositData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'transaction_number',
                        'amount',
                        'status',
                    ]
                ]);

        // Assert database changes
        $this->assertDatabaseHas('transactions', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'type' => 'deposit',
            'amount' => 25000,
            'status' => 'completed',
        ]);

        // Assert account balance updated
        $this->account->refresh();
        $this->assertEquals(75000, $this->account->balance);

        // Assert general ledger entries
        $this->assertDatabaseHas('general_ledger', [
            'account_code' => '1001', // Cash in Hand
            'debit_amount' => 25000,
        ]);

        $this->assertDatabaseHas('general_ledger', [
            'account_code' => '2001', // Member Savings Payable
            'credit_amount' => 25000,
        ]);
    }

    public function test_can_process_withdrawal_transaction(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        $withdrawalData = [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 20000,
            'description' => 'Test withdrawal',
        ];

        $response = $this->postJson('/api/savings/withdrawal', $withdrawalData);

        $response->assertStatus(201);

        // Assert account balance updated (including fee)
        $this->account->refresh();
        $this->assertEquals(29000, $this->account->balance); // 50000 - 20000 - 1000 (fee)

        // Assert transaction recorded with fee
        $this->assertDatabaseHas('transactions', [
            'member_id' => $this->member->id,
            'type' => 'withdrawal',
            'amount' => 20000,
            'fee_amount' => 1000,
            'net_amount' => 19000,
        ]);
    }

    public function test_withdrawal_fails_with_insufficient_balance(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        $withdrawalData = [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 100000, // More than available balance
        ];

        $response = $this->postJson('/api/savings/withdrawal', $withdrawalData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'error',
                ]);

        // Assert no transaction was created
        $this->assertDatabaseMissing('transactions', [
            'member_id' => $this->member->id,
            'type' => 'withdrawal',
            'amount' => 100000,
        ]);

        // Assert balance unchanged
        $this->account->refresh();
        $this->assertEquals(50000, $this->account->balance);
    }

    public function test_double_entry_bookkeeping_always_balances(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        // Process multiple transactions
        $transactions = [
            ['type' => 'deposit', 'amount' => 15000],
            ['type' => 'withdrawal', 'amount' => 10000],
            ['type' => 'deposit', 'amount' => 5000],
        ];

        foreach ($transactions as $txnData) {
            $data = array_merge([
                'member_id' => $this->member->id,
                'account_id' => $this->account->id,
            ], $txnData);

            if ($txnData['type'] === 'deposit') {
                $this->postJson('/api/savings/deposit', $data);
            } else {
                $this->postJson('/api/savings/withdrawal', $data);
            }
        }

        // Check that all ledger entries balance
        $totalDebits = GeneralLedger::where('status', 'posted')->sum('debit_amount');
        $totalCredits = GeneralLedger::where('status', 'posted')->sum('credit_amount');

        $this->assertEquals($totalDebits, $totalCredits, 'General ledger is not balanced');
    }

    public function test_withdrawal_respects_minimum_balance_requirement(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        // Try to withdraw amount that would breach minimum balance
        $withdrawalAmount = $this->account->balance - $this->savingsProduct->minimum_balance + 1000;
        
        $withdrawalData = [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => $withdrawalAmount,
        ];

        $response = $this->postJson('/api/savings/withdrawal', $withdrawalData);

        $response->assertStatus(422);

        // Assert no transaction was created
        $this->assertDatabaseMissing('transactions', [
            'member_id' => $this->member->id,
            'type' => 'withdrawal',
            'amount' => $withdrawalAmount,
        ]);
    }

    public function test_deposit_validation_works_correctly(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        // Test with invalid data
        $invalidData = [
            'member_id' => 99999, // Non-existent member
            'account_id' => $this->account->id,
            'amount' => 500, // Below minimum
        ];

        $response = $this->postJson('/api/savings/deposit', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['member_id', 'amount']);
    }

    public function test_withdrawal_validation_works_correctly(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        // Test with invalid data
        $invalidData = [
            'member_id' => $this->member->id,
            'account_id' => 99999, // Non-existent account
            'amount' => 500, // Below minimum
        ];

        $response = $this->postJson('/api/savings/withdrawal', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['account_id', 'amount']);
    }

    public function test_transaction_history_retrieval(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        // Create some transactions
        $this->postJson('/api/savings/deposit', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 10000,
        ]);

        $this->postJson('/api/savings/withdrawal', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 5000,
        ]);

        // Get transaction history
        $response = $this->getJson("/api/savings/history/{$this->account->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'transaction_number',
                            'type',
                            'amount',
                            'status',
                        ]
                    ]
                ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_account_balance_retrieval(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        $response = $this->getJson("/api/savings/balance/{$this->account->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'account_id',
                        'balance',
                        'available_balance',
                    ]
                ]);

        $this->assertEquals(50000, $response->json('data.balance'));
    }

    public function test_transaction_reversal(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        // Create a transaction first
        $depositResponse = $this->postJson('/api/savings/deposit', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 10000,
        ]);

        $transactionId = $depositResponse->json('data.id');

        // Reverse the transaction
        $response = $this->postJson("/api/savings/reverse/{$transactionId}", [
            'reason' => 'Test reversal - incorrect amount',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Assert original transaction is marked as reversed
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'status' => 'reversed',
        ]);

        // Assert reversal transaction was created
        $this->assertDatabaseHas('transactions', [
            'type' => 'reversal',
            'amount' => -10000,
            'related_account_id' => $transactionId,
        ]);
    }

    public function test_unauthorized_access_denied(): void
    {
        // Test without authentication
        $response = $this->postJson('/api/savings/deposit', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 10000,
        ]);

        $response->assertStatus(401);
    }

    public function test_member_cannot_access_other_member_accounts(): void
    {
        // Create another member
        $otherMember = User::factory()->create(['role' => 'member', 'status' => 'active']);
        
        $this->actingAs($otherMember, 'sanctum');
        
        $response = $this->postJson('/api/savings/deposit', [
            'member_id' => $this->member->id, // Different member
            'account_id' => $this->account->id,
            'amount' => 10000,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['account_id']);
    }

    public function test_daily_limits_are_enforced(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        // Set a low daily limit for testing
        config(['sacco.daily_deposit_limit' => 10000]);
        
        // First deposit should succeed
        $response1 = $this->postJson('/api/savings/deposit', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 8000,
        ]);
        
        $response1->assertStatus(201);
        
        // Second deposit should fail due to daily limit
        $response2 = $this->postJson('/api/savings/deposit', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 5000,
        ]);
        
        $response2->assertStatus(422);
    }

    public function test_transaction_service_integration(): void
    {
        $transactionService = app(TransactionService::class);
        
        $transactionDTO = new \App\DTOs\TransactionDTO(
            memberId: $this->member->id,
            type: 'deposit',
            amount: 15000,
            accountId: $this->account->id,
            description: 'Direct service test',
            processedBy: $this->staff->id
        );

        $transaction = $transactionService->processTransaction($transactionDTO);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals(15000, $transaction->amount);
        
        // Verify account balance was updated
        $this->account->refresh();
        $this->assertEquals(65000, $this->account->balance);
    }

    public function test_ledger_service_creates_correct_entries(): void
    {
        $ledgerService = app(LedgerService::class);
        
        // Create a test transaction
        $transaction = Transaction::create([
            'transaction_number' => 'TXN0000000001',
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'type' => 'deposit',
            'amount' => 10000,
            'status' => 'completed',
            'transaction_date' => now(),
            'processed_by' => $this->staff->id,
        ]);

        $ledgerEntries = [
            new \App\DTOs\LedgerEntryDTO(
                accountCode: '1001',
                accountName: 'Cash in Hand',
                accountType: 'asset',
                debitAmount: 10000,
                creditAmount: 0,
                description: 'Test deposit'
            ),
            new \App\DTOs\LedgerEntryDTO(
                accountCode: '2001',
                accountName: 'Member Savings Payable',
                accountType: 'liability',
                debitAmount: 0,
                creditAmount: 10000,
                description: 'Test deposit'
            ),
        ];

        $ledgerService->createLedgerEntries($transaction, $ledgerEntries);

        // Verify ledger entries were created
        $this->assertDatabaseHas('general_ledger', [
            'account_code' => '1001',
            'debit_amount' => 10000,
            'status' => 'posted',
        ]);

        $this->assertDatabaseHas('general_ledger', [
            'account_code' => '2001',
            'credit_amount' => 10000,
            'status' => 'posted',
        ]);
    }

    public function test_balance_service_calculations(): void
    {
        $balanceService = app(BalanceService::class);
        
        $availableBalance = $balanceService->getAvailableBalance($this->account);
        
        $this->assertEquals(45000, $availableBalance); // 50000 - 5000 (minimum balance)
        
        // Update account balance
        $this->account->updateBalance(10000, 'credit');
        
        $newBalance = $balanceService->getAvailableBalance($this->account);
        $this->assertEquals(55000, $newBalance); // 60000 - 5000 (minimum balance)
    }

    public function test_number_generation_service(): void
    {
        $numberService = app(NumberGenerationService::class);
        
        $transactionNumber = $numberService->generateTransactionNumber('deposit');
        
        $this->assertStringStartsWith('DEP', $transactionNumber);
        $this->assertEquals(13, strlen($transactionNumber)); // DEP + 10 digits
    }

    public function test_validation_service_business_rules(): void
    {
        $validationService = app(ValidationService::class);
        
        $transactionDTO = new \App\DTOs\TransactionDTO(
            memberId: $this->member->id,
            type: 'deposit',
            amount: -100, // Negative amount
            accountId: $this->account->id,
            processedBy: $this->staff->id
        );

        $this->expectException(\App\Exceptions\InvalidTransactionException::class);
        $validationService->validateBusinessRules($transactionDTO);
    }

    public function test_comprehensive_transaction_flow(): void
    {
        $this->actingAs($this->staff, 'sanctum');
        
        // Initial state
        $this->assertEquals(50000, $this->account->balance);
        
        // Deposit 20000
        $depositResponse = $this->postJson('/api/savings/deposit', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 20000,
            'description' => 'Monthly savings',
        ]);
        
        $depositResponse->assertStatus(201);
        $this->account->refresh();
        $this->assertEquals(70000, $this->account->balance);
        
        // Withdraw 15000 (with fee)
        $withdrawalResponse = $this->postJson('/api/savings/withdrawal', [
            'member_id' => $this->member->id,
            'account_id' => $this->account->id,
            'amount' => 15000,
            'description' => 'Emergency withdrawal',
        ]);
        
        $withdrawalResponse->assertStatus(201);
        $this->account->refresh();
        $this->assertEquals(54000, $this->account->balance); // 70000 - 15000 - 1000 (fee)
        
        // Verify transaction records
        $this->assertDatabaseHas('transactions', [
            'type' => 'deposit',
            'amount' => 20000,
            'status' => 'completed',
        ]);
        
        $this->assertDatabaseHas('transactions', [
            'type' => 'withdrawal',
            'amount' => 15000,
            'fee_amount' => 1000,
            'net_amount' => 14000,
            'status' => 'completed',
        ]);
        
        // Verify ledger balance
        $totalDebits = GeneralLedger::where('status', 'posted')->sum('debit_amount');
        $totalCredits = GeneralLedger::where('status', 'posted')->sum('credit_amount');
        $this->assertEquals($totalDebits, $totalCredits);
    }
}