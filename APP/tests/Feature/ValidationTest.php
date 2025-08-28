<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate:fresh');
    $this->artisan('db:seed', ['--class' => 'SaccoDataSeeder']);
    
    $this->member = User::where('email', 'jane@example.com')->first();
    $this->memberToken = auth('api')->login($this->member);
    $this->account = $this->member->accounts()->first();
});

// Registration Validation Tests

test('registration requires all mandatory fields', function () {
    $response = $this->postJson('/api/auth/register', []);

    $response->assertStatus(400)
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password',
            'phone',
            'national_id',
            'date_of_birth',
            'gender',
            'address',
            'occupation',
            'monthly_income'
        ]);
});

test('email must be valid format', function () {
    $invalidEmails = [
        'invalid-email',
        'test@',
        '@example.com',
        'test.example.com',
        'test@example',
    ];

    foreach ($invalidEmails as $invalidEmail) {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => $invalidEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+1234567890',
            'national_id' => 'TEST123456',
            'date_of_birth' => '1990-01-15',
            'gender' => 'male',
            'address' => '123 Test Street',
            'occupation' => 'Tester',
            'monthly_income' => 25000,
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['email']);
    }
});

test('password must be at least 6 characters', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '123',
        'password_confirmation' => '123',
        'phone' => '+1234567890',
        'national_id' => 'TEST123456',
        'date_of_birth' => '1990-01-15',
        'gender' => 'male',
        'address' => '123 Test Street',
        'occupation' => 'Tester',
        'monthly_income' => 25000,
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['password']);
});

test('password confirmation must match', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different_password',
        'phone' => '+1234567890',
        'national_id' => 'TEST123456',
        'date_of_birth' => '1990-01-15',
        'gender' => 'male',
        'address' => '123 Test Street',
        'occupation' => 'Tester',
        'monthly_income' => 25000,
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['password']);
});

test('gender must be valid option', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+1234567890',
        'national_id' => 'TEST123456',
        'date_of_birth' => '1990-01-15',
        'gender' => 'invalid_gender',
        'address' => '123 Test Street',
        'occupation' => 'Tester',
        'monthly_income' => 25000,
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['gender']);
});

test('date of birth must be valid date', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+1234567890',
        'national_id' => 'TEST123456',
        'date_of_birth' => 'invalid-date',
        'gender' => 'male',
        'address' => '123 Test Street',
        'occupation' => 'Tester',
        'monthly_income' => 25000,
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['date_of_birth']);
});

test('monthly income must be numeric', function () {
    $response = $this->postJson('/api/auth/register', [
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
        'monthly_income' => 'not_a_number',
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['monthly_income']);
});

test('name must not exceed 255 characters', function () {
    $longName = str_repeat('a', 256);

    $response = $this->postJson('/api/auth/register', [
        'name' => $longName,
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
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['name']);
});

// Login Validation Tests

test('login requires email and password', function () {
    $response = $this->postJson('/api/auth/login', []);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('login email must be valid format', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'invalid-email',
        'password' => 'password123'
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['email']);
});

// Deposit Validation Tests

test('deposit requires all mandatory fields', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', []);

    $response->assertStatus(400)
        ->assertJsonValidationErrors([
            'account_id',
            'amount',
            'payment_method'
        ]);
});

test('deposit amount must be positive', function () {
    $invalidAmounts = [-100, 0, -0.01, 'not_a_number', ''];

    foreach ($invalidAmounts as $amount) {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->memberToken,
        ])->postJson('/api/savings/deposit', [
            'account_id' => $this->account->id,
            'amount' => $amount,
            'payment_method' => 'cash',
            'description' => 'Test deposit'
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['amount']);
    }
});

test('deposit payment method must be valid', function () {
    $invalidMethods = [
        'invalid_method',
        'credit_card',
        'paypal',
        '',
        123
    ];

    foreach ($invalidMethods as $method) {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->memberToken,
        ])->postJson('/api/savings/deposit', [
            'account_id' => $this->account->id,
            'amount' => 1000,
            'payment_method' => $method,
            'description' => 'Test deposit'
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['payment_method']);
    }
});

test('deposit account_id must exist', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', [
        'account_id' => 99999, // Non-existent account
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => 'Test deposit'
    ]);

    $response->assertStatus(404);
});

test('deposit description length validation', function () {
    $longDescription = str_repeat('a', 256);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'payment_method' => 'cash',
        'description' => $longDescription
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['description']);
});

test('payment reference length validation', function () {
    $longReference = str_repeat('a', 256);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', [
        'account_id' => $this->account->id,
        'amount' => 1000,
        'payment_method' => 'bank_transfer',
        'payment_reference' => $longReference,
        'description' => 'Test deposit'
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['payment_reference']);
});

// Withdrawal Validation Tests

test('withdrawal requires mandatory fields', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', []);

    $response->assertStatus(400)
        ->assertJsonValidationErrors([
            'account_id',
            'amount'
        ]);
});

test('withdrawal amount must be positive', function () {
    $invalidAmounts = [-100, 0, -0.01, 'not_a_number', ''];

    foreach ($invalidAmounts as $amount) {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->memberToken,
        ])->postJson('/api/savings/withdraw', [
            'account_id' => $this->account->id,
            'amount' => $amount,
            'description' => 'Test withdrawal'
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['amount']);
    }
});

test('withdrawal account_id must exist', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/withdraw', [
        'account_id' => 99999, // Non-existent account
        'amount' => 1000,
        'description' => 'Test withdrawal'
    ]);

    $response->assertStatus(404);
});

// Profile Update Validation Tests

test('profile update validates field types', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->putJson('/api/auth/profile', [
        'name' => '', // Empty name
        'phone' => 123, // Invalid phone type
        'monthly_income' => 'not_a_number', // Invalid income type
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors([
            'name',
            'phone',
            'monthly_income'
        ]);
});

test('profile update validates field lengths', function () {
    $longString = str_repeat('a', 256);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->putJson('/api/auth/profile', [
        'name' => $longString,
        'phone' => $longString,
        'address' => $longString,
        'occupation' => $longString,
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors([
            'name',
            'phone',
            'address',
            'occupation'
        ]);
});

test('monthly income must be non-negative', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->putJson('/api/auth/profile', [
        'monthly_income' => -1000
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['monthly_income']);
});

// Password Change Validation Tests

test('password change requires all fields', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/auth/change-password', []);

    $response->assertStatus(400)
        ->assertJsonValidationErrors([
            'current_password',
            'new_password',
            'new_password_confirmation'
        ]);
});

test('new password must meet minimum length', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/auth/change-password', [
        'current_password' => 'password123',
        'new_password' => '123',
        'new_password_confirmation' => '123'
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['new_password']);
});

test('new password confirmation must match', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/auth/change-password', [
        'current_password' => 'password123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'different_password'
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['new_password']);
});

test('current password must be correct', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/auth/change-password', [
        'current_password' => 'wrong_password',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123'
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Current password is incorrect'
        ]);
});

// Member Approval Validation Tests

test('member approval requires action field', function () {
    $admin = User::where('email', 'admin@sacco.com')->first();
    $adminToken = auth('api')->login($admin);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $adminToken,
    ])->postJson('/api/auth/approve-member/1', []);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['action']);
});

test('member approval action must be valid', function () {
    $admin = User::where('email', 'admin@sacco.com')->first();
    $adminToken = auth('api')->login($admin);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $adminToken,
    ])->postJson('/api/auth/approve-member/1', [
        'action' => 'invalid_action'
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['action']);
});

test('rejection requires reason when rejecting', function () {
    $admin = User::where('email', 'admin@sacco.com')->first();
    $adminToken = auth('api')->login($admin);

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
        'Authorization' => 'Bearer ' . $adminToken,
    ])->postJson("/api/auth/approve-member/{$pendingUser->id}", [
        'action' => 'reject'
        // Missing rejection_reason
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['rejection_reason']);
});

// Data Type Validation Tests

test('numeric fields reject string values', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->postJson('/api/savings/deposit', [
        'account_id' => 'not_a_number',
        'amount' => 'also_not_a_number',
        'payment_method' => 'cash',
        'description' => 'Test deposit'
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['account_id', 'amount']);
});

test('date fields validate format', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+1234567890',
        'national_id' => 'TEST123456',
        'date_of_birth' => '1990-13-32', // Invalid date
        'gender' => 'male',
        'address' => '123 Test Street',
        'occupation' => 'Tester',
        'monthly_income' => 25000,
    ]);

    $response->assertStatus(400)
        ->assertJsonValidationErrors(['date_of_birth']);
});

// Security Validation Tests

test('SQL injection attempts are sanitized', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => "admin@sacco.com'; DROP TABLE users; --",
        'password' => 'password123'
    ]);

    // Should either reject the input or handle it safely
    expect($response->status())->toBeIn([400, 401]);
    
    // Verify users table still exists
    $this->assertDatabaseHas('users', [
        'email' => 'admin@sacco.com'
    ]);
});

test('XSS attempts in input fields are sanitized', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->memberToken,
    ])->putJson('/api/auth/profile', [
        'name' => '<script>alert("XSS")</script>',
        'address' => '<img src="x" onerror="alert(1)">'
    ]);

    if ($response->status() === 200) {
        // If accepted, verify data is sanitized
        $user = User::find($this->member->id);
        expect($user->name)->not->toContain('<script>');
        expect($user->address)->not->toContain('<img');
    } else {
        // Or input should be rejected
        $response->assertStatus(400);
    }
});