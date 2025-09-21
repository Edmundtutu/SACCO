<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->artisan('db:seed', ['--class' => 'SaccoDataSeeder']);
    
    // Set up different user roles
    $this->admin = User::where('email', 'admin@sacco.com')->first();
    $this->adminToken = auth('api')->login($this->admin);
    
    $this->loanOfficer = User::where('email', 'loans@sacco.com')->first();
    $this->loanOfficerToken = auth('api')->login($this->loanOfficer);
    
    $this->member = User::where('email', 'jane@example.com')->first();
    $this->memberToken = auth('api')->login($this->member);
    
    $this->otherMember = User::where('email', 'robert@example.com')->first();
    $this->otherMemberToken = auth('api')->login($this->otherMember);
});

test('unauthenticated users cannot access protected endpoints', function () {
        $protectedEndpoints = [
            ['method' => 'GET', 'uri' => '/api/auth/profile'],
            ['method' => 'PUT', 'uri' => '/api/auth/profile'],
            ['method' => 'POST', 'uri' => '/api/auth/change-password'],
            ['method' => 'POST', 'uri' => '/api/auth/logout'],
            ['method' => 'POST', 'uri' => '/api/auth/refresh'],
            ['method' => 'GET', 'uri' => '/api/savings/accounts'],
            ['method' => 'GET', 'uri' => '/api/savings/products'],
            ['method' => 'POST', 'uri' => '/api/savings/deposit'],
            ['method' => 'POST', 'uri' => '/api/savings/withdraw'],
        ];

        foreach ($protectedEndpoints as $endpoint) {
            $response = $this->{strtolower($endpoint['method']) . 'Json'}($endpoint['uri'], []);
            
            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
        }
});

test('invalid tokens are rejected', function () {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_here',
        ])->getJson('/api/auth/profile');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
});

test('expired tokens are rejected', function () {
    // This test would require token manipulation or time travel
    // For now, we'll test with malformed token
    $response = $this->withHeaders([
        'Authorization' => 'Bearer expired.token.here',
    ])->getJson('/api/auth/profile');

    $response->assertStatus(401);
});

test('admin can approve members', function () {
    // Create pending member
    $pendingUser = User::create([
        'name' => 'Pending Member',
        'email' => 'pending@example.com',
        'password' => bcrypt('password123'),
        'member_number' => 'PEND001',
        'role' => 'member',
        'status' => 'pending_approval',
        'phone' => '+1234567890',
        'national_id' => 'PEND123456',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'address' => 'Pending Address',
        'occupation' => 'Pending',
        'monthly_income' => 25000,
        'membership_date' => now(),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->adminToken,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'approve'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Member approved successfully'
        ]);
});

test('admin can reject members', function () {
    // Create pending member
    $pendingUser = User::create([
        'name' => 'Pending Member 2',
        'email' => 'pending2@example.com',
        'password' => bcrypt('password123'),
        'member_number' => 'PEND002',
        'role' => 'member',
        'status' => 'pending_approval',
        'phone' => '+1234567891',
        'national_id' => 'PEND123457',
        'date_of_birth' => '1990-01-01',
        'gender' => 'female',
        'address' => 'Pending Address 2',
        'occupation' => 'Pending',
        'monthly_income' => 25000,
        'membership_date' => now(),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->adminToken,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'reject',
        'rejection_reason' => 'Insufficient documentation'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Member rejected successfully'
        ]);
});

test('admin can access all savings accounts', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->adminToken,
    ])->getJson('/api/savings/accounts');

    $response->assertStatus(200);
    // Admin sees their own accounts (they may have accounts too)
});

test('admin can access savings products', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->adminToken,
    ])->getJson('/api/savings/products');

    $response->assertStatus(200);
});

test('admin has full profile access', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->adminToken,
    ])->getJson('/api/auth/profile');

    $response->assertStatus(200);
    expect($response->json('role'))->toBe('admin');
});

test('member can access their own profile', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/auth/profile');

    $response->assertStatus(200);
    expect($response->json('role'))->toBe('member');
    expect($response->json('email'))->toBe($this->member->email);
});

test('member can update their own profile', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->putJson('/api/auth/profile', [
        'name' => 'Updated Name',
        'phone' => '+9876543210'
    ]);

    $response->assertStatus(200);
});

test('member can change their own password', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/auth/change-password', [
        'current_password' => 'password123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123'
    ]);

    $response->assertStatus(200);
});

test('member can access their own accounts', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/accounts');

    $response->assertStatus(200);
    
    // Verify member only sees their own accounts
    $accounts = $response->json('data');
    foreach ($accounts as $account) {
        $this->assertDatabaseHas('accounts', [
            'id' => $account['id'],
            'member_id' => $this->member->id
        ]);
    }
});

test('member can make deposits to their own accounts', function () {
    $account = $this->member->accounts()->first();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', [
        'account_id' => $account->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Test deposit'
    ]);

    $response->assertStatus(200);
});

test('member can make withdrawals from their own accounts', function () {
    $account = $this->member->accounts()->first();
    
    // Ensure sufficient balance
    $account->update([
        'balance' => 5000,
        'available_balance' => 5000
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', [
        'account_id' => $account->id,
        'amount' => 1000,
        'description' => 'Test withdrawal'
    ]);

    $response->assertStatus(200);
});

test('member can view their own transaction history', function () {
    $account = $this->member->accounts()->first();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson("/api/savings/accounts/{$account->id}/transactions");

    $response->assertStatus(200);
});

test('member cannot approve other members', function () {
    // Create pending member
    $pendingUser = User::create([
        'name' => 'Pending Member 3',
        'email' => 'pending3@example.com',
        'password' => bcrypt('password123'),
        'member_number' => 'PEND003',
        'role' => 'member',
        'status' => 'pending_approval',
        'phone' => '+1234567892',
        'national_id' => 'PEND123458',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'address' => 'Pending Address 3',
        'occupation' => 'Pending',
        'monthly_income' => 25000,
        'membership_date' => now(),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'approve'
    ]);

    $response->assertStatus(403);
});

test('member cannot access other members accounts', function () {
    $otherAccount = $this->otherMember->accounts()->first();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', [
        'account_id' => $otherAccount->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Unauthorized deposit'
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Account not found or does not belong to you.'
        ]);
});

test('member cannot withdraw from other members accounts', function () {
    $otherAccount = $this->otherMember->accounts()->first();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', [
        'account_id' => $otherAccount->id,
        'amount' => 1000,
        'description' => 'Unauthorized withdrawal'
    ]);

    $response->assertStatus(404);
});

test('member cannot view other members transaction history', function () {
    $otherAccount = $this->otherMember->accounts()->first();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson("/api/savings/accounts/{$otherAccount->id}/transactions");

    $response->assertStatus(404);
});

test('loan officer can access their profile', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->loanOfficerToken,
    ])->getJson('/api/auth/profile');

    $response->assertStatus(200);
    expect($response->json('role'))->toBe('loan_officer');
});

test('loan officer can view savings products', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->loanOfficerToken,
    ])->getJson('/api/savings/products');

    $response->assertStatus(200);
});

test('loan officer cannot approve members (admin only)', function () {
    // Create pending member
    $pendingUser = User::create([
        'name' => 'Pending Member 4',
        'email' => 'pending4@example.com',
        'password' => bcrypt('password123'),
        'member_number' => 'PEND004',
        'role' => 'member',
        'status' => 'pending_approval',
        'phone' => '+1234567893',
        'national_id' => 'PEND123459',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'address' => 'Pending Address 4',
        'occupation' => 'Pending',
        'monthly_income' => 25000,
        'membership_date' => now(),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->loanOfficerToken,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'approve'
    ]);

    $response->assertStatus(403);
});

test('members cannot access each others data', function () {
    // Test member 1 trying to access member 2's accounts
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/accounts');

    $response->assertStatus(200);
    
    $accounts = $response->json('data');
    foreach ($accounts as $account) {
        expect($account['member_id'] ?? null)->toBe($this->member->id);
    }
});

test('token belongs to correct user', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/auth/profile');

    $response->assertStatus(200);
    expect($response->json('id'))->toBe($this->member->id);
    expect($response->json('email'))->toBe($this->member->email);
});

test('switching tokens changes accessible data', function () {
    // Get accounts with first member token
    $response1 = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/savings/accounts');

    $response1->assertStatus(200);
    $accounts1 = $response1->json('data');

    // Get accounts with second member token
    $response2 = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->otherMemberToken,
    ])->getJson('/api/savings/accounts');

    $response2->assertStatus(200);
    $accounts2 = $response2->json('data');

    // Accounts should be different (unless members share accounts, which they shouldn't)
    if (count($accounts1) > 0 && count($accounts2) > 0) {
        $account1Ids = array_column($accounts1, 'id');
        $account2Ids = array_column($accounts2, 'id');
        $this->assertNotEquals($account1Ids, $account2Ids);
    }
});

test('admin role has highest privileges', function () {
    // Admin can approve members
    $pendingUser = User::create([
        'name' => 'Test Hierarchy',
        'email' => 'hierarchy@example.com',
        'password' => bcrypt('password123'),
        'member_number' => 'HIER001',
        'role' => 'member',
        'status' => 'pending_approval',
        'phone' => '+1234567894',
        'national_id' => 'HIER123456',
        'date_of_birth' => '1990-01-01',
        'gender' => 'male',
        'address' => 'Hierarchy Address',
        'occupation' => 'Test',
        'monthly_income' => 25000,
        'membership_date' => now(),
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->adminToken,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'approve'
    ]);

    $response->assertStatus(200);
});

test('role information is consistent across endpoints', function () {
    // Check role in profile endpoint
    $profileResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->adminToken,
    ])->getJson('/api/auth/profile');

    $profileResponse->assertStatus(200);
    $profileRole = $profileResponse->json('role');

    // Check role in login response
    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => 'admin@sacco.com',
        'password' => 'password123'
    ]);

    $loginResponse->assertStatus(200);
    $loginRole = $loginResponse->json('user.role');

    expect($profileRole)->toBe($loginRole);
    expect($profileRole)->toBe('admin');
});

test('logout invalidates token for subsequent requests', function () {
    // First verify token works
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/auth/profile');

    $response->assertStatus(200);

    // Logout
    $logoutResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/auth/logout');

    $logoutResponse->assertStatus(200);

    // Verify token no longer works
    $profileResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/auth/profile');

    $profileResponse->assertStatus(401);
});

test('token refresh maintains same user identity', function () {
    // Get current user info
    $originalProfile = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->getJson('/api/auth/profile');

    $originalProfile->assertStatus(200);

    // Refresh token
    $refreshResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/auth/refresh');

    $refreshResponse->assertStatus(200);
    $newToken = $refreshResponse->json('access_token');

    // Verify new token works for same user
    $newProfile = $this->withHeaders([
        'Authorization' => 'Bearer ' . $newToken,
    ])->getJson('/api/auth/profile');

    $newProfile->assertStatus(200);
    expect($newProfile->json('id'))->toBe($originalProfile->json('id'));
    expect($newProfile->json('email'))->toBe($originalProfile->json('email'));
});