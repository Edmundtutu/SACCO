<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->artisan('db:seed', ['--class' => 'SaccoDataSeeder']);
});

// User Registration Tests

test('new member can register successfully', function () {
    $memberData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+1234567890',
        'national_id' => 'TEST123456',
        'date_of_birth' => '1990-01-15',
        'gender' => 'male',
        'address' => '123 Test Street',
        'occupation' => 'Tester',
        'monthly_income' => 25000,
        'next_of_kin_name' => 'Test Kin',
        'next_of_kin_relationship' => 'Sibling',
        'next_of_kin_phone' => '+1234567891',
        'next_of_kin_address' => '123 Kin Street'
    ];

    $response = $this->postJson('/api/auth/register', $memberData);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'User successfully registered. Awaiting admin approval.'
        ])
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'member_number',
                'status'
            ]
        ]);

    expect($response->json('user.status'))->toBe('pending_approval');
    expect($response->json('user.email'))->toBe('test@example.com');
    
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'status' => 'pending_approval'
    ]);
});

test('registration fails with invalid data', function () {
    $invalidData = [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123',
        'phone' => '',
        'national_id' => '',
    ];

    $response = $this->postJson('/api/auth/register', $invalidData);

    $response->assertStatus(400)
        ->assertJsonStructure([
            'name',
            'email',
            'password',
            'phone',
            'national_id'
        ]);
});

test('registration fails with duplicate email', function () {
    $memberData = [
        'name' => 'Test User',
        'email' => 'admin@sacco.com', // Existing email from seeder
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+1234567890',
        'national_id' => 'TEST123456',
        'date_of_birth' => '1990-01-15',
        'gender' => 'male',
        'address' => '123 Test Street',
        'occupation' => 'Tester',
        'monthly_income' => 25000,
    ];

    $response = $this->postJson('/api/auth/register', $memberData);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['email']);
});

// User Login Tests

test('admin can login successfully', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'admin@sacco.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => [
                'id',
                'name',
                'email',
                'member_number',
                'role',
                'status'
            ]
        ]);

    expect($response->json('user.role'))->toBe('admin');
    expect($response->json('user.status'))->toBe('active');
    expect($response->json('token_type'))->toBe('bearer');
});

test('member can login successfully', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user'
        ]);

    expect($response->json('user.role'))->toBe('member');
    expect($response->json('user.status'))->toBe('active');
});

test('login fails with invalid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'admin@sacco.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'error' => 'Unauthorized'
        ]);
});

test('login fails with non-existent user', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'error' => 'Unauthorized'
        ]);
});

test('pending approval member cannot login', function () {
    // Create a pending user
    $user = User::create([
        'name' => 'Pending User',
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

    $response = $this->postJson('/api/auth/login', [
        'email' => 'pending@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'error' => 'Account pending approval'
        ]);
});

// User Profile Management Tests

test('authenticated user can get profile', function () {
    $user = User::where('email', 'admin@sacco.com')->first();
    $token = auth('api')->login($user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/profile');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'member_number',
            'role',
            'status',
            'phone',
            'total_shares',
            'total_savings_balance',
            'active_loan_balance'
        ]);

    expect($response->json('email'))->toBe('admin@sacco.com');
});

test('unauthenticated user cannot get profile', function () {
    $response = $this->getJson('/api/auth/profile');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]);
});

test('authenticated user can update profile', function () {
    $user = User::where('email', 'jane@example.com')->first();
    $token = auth('api')->login($user);

    $updateData = [
        'name' => 'Jane Updated Doe',
        'phone' => '+9876543210',
        'address' => '456 Updated Address',
        'occupation' => 'Senior Engineer',
        'monthly_income' => 45000
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/auth/profile', $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Profile updated successfully'
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Jane Updated Doe',
        'phone' => '+9876543210',
        'monthly_income' => 45000
    ]);
});

test('user can change password', function () {
    $user = User::where('email', 'jane@example.com')->first();
    $token = auth('api')->login($user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/auth/change-password', [
        'current_password' => 'password123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Password changed successfully'
        ]);

    // Verify old password no longer works
    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password123'
    ]);
    $loginResponse->assertStatus(401);

    // Verify new password works
    $newLoginResponse = $this->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'newpassword123'
    ]);
    $newLoginResponse->assertStatus(200);
});

// Member Approval Process Tests

test('admin can approve pending member', function () {
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

    $admin = User::where('email', 'admin@sacco.com')->first();
    $token = auth('api')->login($admin);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'approve'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Member approved successfully'
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $pendingUser->id,
        'status' => 'active',
        'approved_by' => $admin->id
    ]);
});

test('admin can reject pending member', function () {
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

    $admin = User::where('email', 'admin@sacco.com')->first();
    $token = auth('api')->login($admin);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'reject',
        'rejection_reason' => 'Insufficient documentation'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Member rejected successfully'
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $pendingUser->id,
        'status' => 'rejected'
    ]);
});

test('non-admin cannot approve members', function () {
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

    $member = User::where('email', 'jane@example.com')->first();
    $token = auth('api')->login($member);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'approve'
    ]);

    $response->assertStatus(403);
});

// Token Management Tests

test('user can refresh token', function () {
    $user = User::where('email', 'admin@sacco.com')->first();
    $token = auth('api')->login($user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/auth/refresh');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);

    expect($response->json('token_type'))->toBe('bearer');
});

test('user can logout', function () {
    $user = User::where('email', 'admin@sacco.com')->first();
    $token = auth('api')->login($user);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out'
        ]);

    // Verify token is invalidated
    $profileResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/auth/profile');

    $profileResponse->assertStatus(401);
});