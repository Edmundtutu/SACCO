<?php

use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->artisan('db:seed', ['--class' => 'SaccoDataSeeder']);
    
    // Get authenticated member for testing
    $this->member = User::where('email', 'jane@example.com')->first();
    $this->memberToken = auth('api')->login($this->member);
    
    // Get admin for testing admin operations
    $this->admin = User::where('email', 'admin@sacco.com')->first();
    $this->adminToken = auth('api')->login($this->admin);
    
    // Get member's account for testing
    $this->account = $this->member->accounts()->first();
});

// Savings Products Tests

test('authenticated user can get savings products', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'code',
                    'description',
                    'type',
                    'minimum_balance',
                    'interest_rate',
                    'is_active'
                ]
            ]
        ]);

    expect($response->json('success'))->toBe(true);
    expect(count($response->json('data')))->toBeGreaterThan(0);
});

test('unauthenticated user cannot get savings products', function () {
    $response = $this->getJson('/api/savings/products');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]);
});

test('savings products have required fields', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/products');

    $response->assertStatus(200);
    
    $products = $response->json('data');
    expect($products)->toBeArray();
    
    foreach ($products as $product) {
        expect($product)->toHaveKeys([
            'id', 'name', 'code', 'type', 'minimum_balance', 
            'interest_rate', 'is_active'
        ]);
        expect($product['is_active'])->toBe(true);
    }
});

// Member Accounts Tests

test('authenticated member can get their accounts', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/accounts');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'account_number',
                    'balance',
                    'available_balance',
                    'minimum_balance',
                    'status',
                    'savings_product'
                ]
            ]
        ]);

    expect($response->json('success'))->toBe(true);
    expect(count($response->json('data')))->toBeGreaterThan(0);
});

test('member only sees their own accounts', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/accounts');

    $response->assertStatus(200);
    
    $accounts = $response->json('data');
    foreach ($accounts as $account) {
        $dbAccount = Account::find($account['id']);
        expect($dbAccount->member_id)->toBe($this->member->id);
    }
});

test('account balances are properly formatted', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/accounts');

    $response->assertStatus(200);
    
    $accounts = $response->json('data');
    foreach ($accounts as $account) {
        expect($account['balance'])->toBeString();
        expect($account['available_balance'])->toBeString();
        expect($account['minimum_balance'])->toBeString();
        expect(is_numeric($account['balance']))->toBe(true);
    }
});

// Deposit Operations Tests

test('member can make valid cash deposit', function () {
    $initialBalance = $this->account->balance;
    
    $depositData = [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Test cash deposit'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', $depositData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'account',
            'transaction'
        ])
        ->assertJson([
            'message' => 'Deposit successful.'
        ]);

    // Verify account balance updated
    $this->account->refresh();
    expect($this->account->balance)->toBe($initialBalance + 1000);

    // Verify transaction created
    $this->assertDatabaseHas('transactions', [
        'member_id' => $this->member->id,
        'account_id' => $this->account->id,
        'type' => 'deposit',
        'amount' => 1000,
        'status' => 'completed'
    ]);
});

test('member can make bank transfer deposit with reference', function () {
    $initialBalance = $this->account->balance;
    
    $depositData = [
        'account_id' => $this->account->id,
        'amount' => 5000,
        'payment_method' => 'bank_transfer',
        'payment_reference' => 'BANK_REF_12345',
        'description' => 'Salary deposit'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', $depositData);

    $response->assertStatus(200);

    // Verify transaction has reference
    $this->assertDatabaseHas('transactions', [
        'member_id' => $this->member->id,
        'payment_method' => 'bank_transfer',
        'payment_reference' => 'BANK_REF_12345',
        'amount' => 5000
    ]);
});

test('deposit fails with invalid account', function () {
    $depositData = [
        'account_id' => 99999, // Non-existent account
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Test deposit'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', $depositData);

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Account not found or does not belong to you.'
        ]);
});

test('deposit fails with negative amount', function () {
    $depositData = [
        'account_id' => $this->account->id,
        'amount' => -500,
        'payment_method' => 'cash',
        'description' => 'Invalid deposit'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', $depositData);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['amount']);
});

test('deposit fails with invalid payment method', function () {
    $depositData = [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'payment_method' => 'invalid_method',
        'description' => 'Test deposit'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', $depositData);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['payment_method']);
});

test('member cannot deposit to another members account', function () {
    // Get another member's account
    $otherMember = User::where('email', 'robert@example.com')->first();
    $otherAccount = $otherMember->accounts()->first();

    $depositData = [
        'account_id' => $otherAccount->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Unauthorized deposit'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', $depositData);

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Account not found or does not belong to you.'
        ]);
});

// Withdrawal Operations Tests

test('member can make valid withdrawal', function () {
    // First make a deposit to ensure sufficient balance
    $this->account->balance = 5000;
    $this->account->available_balance = 5000;
    $this->account->save();

    $withdrawalData = [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'description' => 'Test withdrawal'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', $withdrawalData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'account',
            'transaction'
        ])
        ->assertJson([
            'message' => 'Withdrawal successful.'
        ]);

    // Verify transaction created
    $this->assertDatabaseHas('transactions', [
        'member_id' => $this->member->id,
        'account_id' => $this->account->id,
        'type' => 'withdrawal',
        'amount' => 1000,
        'status' => 'completed'
    ]);
});

test('withdrawal fails with insufficient balance', function () {
    // Set low balance
    $this->account->balance = 500;
    $this->account->available_balance = 500;
    $this->account->save();

    $withdrawalData = [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'description' => 'Test withdrawal'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', $withdrawalData);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Insufficient available balance.'
        ]);
});

test('withdrawal fails below minimum balance', function () {
    // Set balance just above minimum
    $this->account->balance = 1200;
    $this->account->available_balance = 1200;
    $this->account->minimum_balance = 1000;
    $this->account->save();

    $withdrawalData = [
        'account_id' => $this->account->id,
        'amount' => 500, // This would take balance below minimum
        'description' => 'Test withdrawal'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', $withdrawalData);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Withdrawal would breach minimum balance requirement.'
        ]);
});

test('withdrawal fails with inactive account', function () {
    // Set account as inactive
    $this->account->status = 'inactive';
    $this->account->save();

    $withdrawalData = [
        'account_id' => $this->account->id,
        'amount' => 100,
        'description' => 'Test withdrawal'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', $withdrawalData);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Account is not active.'
        ]);
});

test('withdrawal includes fee calculation', function () {
    // Set sufficient balance
    $this->account->balance = 5000;
    $this->account->available_balance = 5000;
    $this->account->save();

    $withdrawalData = [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'description' => 'Test withdrawal with fee'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', $withdrawalData);

    $response->assertStatus(200);

    $transaction = $response->json('transaction');
    expect($transaction['fee_amount'])->toBeNumeric();
    expect((float)$transaction['fee_amount'])->toBeGreaterThanOrEqual(0);
});

// Transaction History Tests

test('member can get account transaction history', function () {
    // Create some transactions first
    $this->postJson('/api/savings/deposit', [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Test deposit 1'
    ], ['Authorization' => 'Bearer ' . $this->memberToken]);

    $this->postJson('/api/savings/deposit', [
        'account_id' => $this->account->id,
        'amount' => 500,
        'payment_method' => 'bank_transfer',
        'description' => 'Test deposit 2'
    ], ['Authorization' => 'Bearer ' . $this->memberToken]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson("/api/savings/accounts/{$this->account->id}/transactions");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'transaction_number',
                        'type',
                        'amount',
                        'balance_before',
                        'balance_after',
                        'description',
                        'payment_method',
                        'status',
                        'transaction_date'
                    ]
                ],
                'total'
            ]
        ]);

    expect($response->json('success'))->toBe(true);
    expect(count($response->json('data.data')))->toBeGreaterThan(0);
});

test('transaction history can be filtered by type', function () {
    // Create different types of transactions
    $this->postJson('/api/savings/deposit', [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Test deposit'
    ], ['Authorization' => 'Bearer ' . $this->memberToken]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson("/api/savings/accounts/{$this->account->id}/transactions?type=deposit");

    $response->assertStatus(200);
    
    $transactions = $response->json('data.data');
    foreach ($transactions as $transaction) {
        expect($transaction['type'])->toBe('deposit');
    }
});

test('transaction history can be filtered by date range', function () {
    $startDate = now()->startOfMonth()->toDateString();
    $endDate = now()->endOfMonth()->toDateString();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson("/api/savings/accounts/{$this->account->id}/transactions?start_date={$startDate}&end_date={$endDate}");

    $response->assertStatus(200);
});

test('member cannot access another members transaction history', function () {
    $otherMember = User::where('email', 'robert@example.com')->first();
    $otherAccount = $otherMember->accounts()->first();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson("/api/savings/accounts/{$otherAccount->id}/transactions");

    $response->assertStatus(404);
});

test('transaction history is paginated', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson("/api/savings/accounts/{$this->account->id}/transactions?page=1");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]
        ]);

    expect($response->json('data.current_page'))->toBe(1);
    expect($response->json('data.per_page'))->toBeInt();
});

// Balance Calculations Tests

test('account balance updates correctly after transactions', function () {
    $initialBalance = $this->account->balance;

    // Make deposit
    $this->postJson('/api/savings/deposit', [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Balance test deposit'
    ], ['Authorization' => 'Bearer ' . $this->memberToken]);

    $this->account->refresh();
    expect($this->account->balance)->toBe($initialBalance + 1000);
    expect($this->account->available_balance)->toBe($initialBalance + 1000);

    // Make withdrawal (if sufficient balance)
    if ($this->account->balance > 1500) {
        $balanceBeforeWithdrawal = $this->account->balance;
        
        $this->postJson('/api/savings/withdraw', [
            'account_id' => $this->account->id,
            'amount' => 500,
            'description' => 'Balance test withdrawal'
        ], ['Authorization' => 'Bearer ' . $this->memberToken]);

        $this->account->refresh();
        expect($this->account->balance)->toBeLessThan($balanceBeforeWithdrawal);
    }
});

test('transaction records accurate balance snapshots', function () {
    $balanceBefore = $this->account->balance;

    $response = $this->postJson('/api/savings/deposit', [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Balance snapshot test'
    ], ['Authorization' => 'Bearer ' . $this->memberToken]);

    $response->assertStatus(200);

    $transaction = $response->json('transaction');
    expect((float)$transaction['balance_before'])->toBe($balanceBefore);
    expect((float)$transaction['balance_after'])->toBe($balanceBefore + 1000);
});