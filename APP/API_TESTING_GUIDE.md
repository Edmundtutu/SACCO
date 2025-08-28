# SACCO API Testing Guide

This guide provides comprehensive instructions for testing the SACCO REST API using various tools and methods.

## Table of Contents

1. [Quick Start Testing](#quick-start-testing)
2. [Using the Built-in Test Script](#using-the-built-in-test-script)
3. [Testing with Postman](#testing-with-postman)
4. [Testing with cURL](#testing-with-curl)
5. [Testing with Insomnia](#testing-with-insomnia)
6. [Automated Testing](#automated-testing)
7. [Test Scenarios](#test-scenarios)
8. [Common Issues](#common-issues)

---

## Quick Start Testing

### Prerequisites
1. Laravel server running: `php artisan serve`
2. Database migrated and seeded: `php artisan migrate:fresh --seed --seeder=SaccoDataSeeder`

### Test Accounts
```
Admin: admin@sacco.com / password123
Loan Officer: loans@sacco.com / password123
Members: jane@example.com, robert@example.com, mary@example.com / password123
```

---

## Using the Built-in Test Script

The easiest way to test the API is using the included PHP test script:

### Run Basic Tests
```bash
cd /workspace/APP
php test_api.php
```

### Expected Output
```
🚀 Starting SACCO API Basic Tests
================================

🔐 Testing Login...
✅ Login successful! Token received.
   User: System Administrator
   Role: admin

👤 Testing Get Profile...
✅ Profile retrieved successfully!
   Name: System Administrator
   Member Number: ADMIN001
   Status: active

🏦 Testing Get Accounts...
✅ Accounts retrieved successfully!
   Number of accounts: 3
   - Account: SA00000001 | Balance: 21668 | Status: active
   - Account: SA00000002 | Balance: 43488 | Status: active
   - Account: SA00000003 | Balance: 17891 | Status: active

📦 Testing Get Savings Products...
✅ Savings products retrieved successfully!
   Number of products: 3
   - Compulsory Savings (compulsory) - 5% interest
   - Voluntary Savings (voluntary) - 3% interest
   - Fixed Deposit (fixed_deposit) - 8% interest

💰 Testing Deposit...
✅ Deposit successful!
   Amount: 500
   New Balance: 22168
   Transaction ID: 1

🎉 Basic tests completed!
```

---

## Testing with Postman

### Step 1: Import Collection
1. Open Postman
2. Click **Import**
3. Select `SACCO_API_Postman_Collection.json`
4. Collection will be imported with all endpoints

### Step 2: Set Environment Variables
Create a new environment with:
```json
{
  "base_url": "http://localhost:8000/api",
  "auth_token": ""
}
```

### Step 3: Login and Get Token
1. Run **Authentication > Login Admin**
2. Token will be automatically saved to environment
3. Now you can test protected endpoints

### Step 4: Test Member Registration
```json
POST {{base_url}}/auth/register
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "national_id": "TEST123456",
  "date_of_birth": "1990-01-15",
  "gender": "male",
  "address": "123 Test Street",
  "occupation": "Tester",
  "monthly_income": 25000,
  "next_of_kin_name": "Test Kin",
  "next_of_kin_relationship": "Sibling",
  "next_of_kin_phone": "+1234567891",
  "next_of_kin_address": "123 Kin Street"
}
```

### Step 5: Test Savings Operations
1. **Get Accounts**: `GET {{base_url}}/savings/accounts`
2. **Make Deposit**: `POST {{base_url}}/savings/deposit`
3. **Make Withdrawal**: `POST {{base_url}}/savings/withdraw`
4. **Get Transactions**: `GET {{base_url}}/savings/accounts/1/transactions`

---

## Testing with cURL

### Login and Get Token
```bash
# Login
response=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@sacco.com",
    "password": "password123"
  }')

# Extract token
token=$(echo $response | jq -r '.data.token')
echo "Token: $token"
```

### Test Protected Endpoints
```bash
# Get profile
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer $token" \
  -H "Content-Type: application/json"

# Get accounts
curl -X GET http://localhost:8000/api/savings/accounts \
  -H "Authorization: Bearer $token" \
  -H "Content-Type: application/json"

# Make deposit
curl -X POST http://localhost:8000/api/savings/deposit \
  -H "Authorization: Bearer $token" \
  -H "Content-Type: application/json" \
  -d '{
    "account_id": 1,
    "amount": 1000,
    "payment_method": "cash",
    "description": "Test deposit"
  }'
```

### Complete Member Registration Flow
```bash
# 1. Register new member
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Alice Johnson",
    "email": "alice@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "national_id": "ALICE123456",
    "date_of_birth": "1992-03-15",
    "gender": "female",
    "address": "789 Test Street",
    "occupation": "Nurse",
    "monthly_income": 35000,
    "next_of_kin_name": "Bob Johnson",
    "next_of_kin_relationship": "Husband",
    "next_of_kin_phone": "+1234567899",
    "next_of_kin_address": "789 Test Street"
  }'

# 2. Admin login
admin_response=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@sacco.com",
    "password": "password123"
  }')
admin_token=$(echo $admin_response | jq -r '.data.token')

# 3. Approve member (replace 4 with actual member ID)
curl -X POST http://localhost:8000/api/auth/approve-member/4 \
  -H "Authorization: Bearer $admin_token" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "approve"
  }'

# 4. Member login
member_response=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "alice@test.com",
    "password": "password123"
  }')
member_token=$(echo $member_response | jq -r '.data.token')

# 5. Member makes deposit
curl -X POST http://localhost:8000/api/savings/deposit \
  -H "Authorization: Bearer $member_token" \
  -H "Content-Type: application/json" \
  -d '{
    "account_id": 1,
    "amount": 5000,
    "payment_method": "bank_transfer",
    "payment_reference": "BANK_REF_001",
    "description": "Initial deposit"
  }'
```

---

## Testing with Insomnia

### Step 1: Import OpenAPI Spec
1. Open Insomnia
2. Create new Request Collection
3. Import from **OpenAPI** > Select `openapi.yaml`
4. All endpoints will be automatically created

### Step 2: Configure Environment
Create environment variables:
```json
{
  "base_url": "http://localhost:8000/api",
  "auth_token": ""
}
```

### Step 3: Set Bearer Token
1. Go to **Authentication** tab in any protected request
2. Select **Bearer Token**
3. Token: `{{ auth_token }}`

---

## Automated Testing

### PHPUnit Tests
Create automated tests for the API:

```php
// tests/Feature/AuthTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_register()
    {
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
            'monthly_income' => 25000,
            'next_of_kin_name' => 'Test Kin',
            'next_of_kin_relationship' => 'Sibling',
            'next_of_kin_phone' => '+1234567891',
            'next_of_kin_address' => '123 Kin Street'
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Registration successful. Your membership is pending approval.'
                ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user',
                        'token',
                        'token_type',
                        'expires_in'
                    ]
                ]);
    }
}
```

### Run Tests
```bash
php artisan test --filter AuthTest
```

---

## Test Scenarios

### Scenario 1: New Member Journey
1. **Register** new member
2. **Admin login** and approve member
3. **Member login** with credentials
4. **View** available savings products
5. **Make deposit** to create account
6. **Check balance** after deposit
7. **Make withdrawal** (test minimum balance rules)
8. **View transaction history**

### Scenario 2: Role-Based Access Testing
```bash
# Test member accessing admin endpoints (should fail)
curl -X POST http://localhost:8000/api/auth/approve-member/1 \
  -H "Authorization: Bearer $member_token" \
  -H "Content-Type: application/json" \
  -d '{"action": "approve"}'

# Expected: 403 Forbidden
```

### Scenario 3: Validation Testing
```bash
# Test invalid data (should return validation errors)
curl -X POST http://localhost:8000/api/savings/deposit \
  -H "Authorization: Bearer $token" \
  -H "Content-Type: application/json" \
  -d '{
    "account_id": "invalid",
    "amount": -100,
    "payment_method": "invalid_method"
  }'

# Expected: 422 Unprocessable Entity with validation errors
```

### Scenario 4: Business Logic Testing
```bash
# Test withdrawal exceeding balance
curl -X POST http://localhost:8000/api/savings/withdraw \
  -H "Authorization: Bearer $token" \
  -H "Content-Type: application/json" \
  -d '{
    "account_id": 1,
    "amount": 999999999,
    "description": "Test overdraft"
  }'

# Expected: 400 Bad Request - insufficient funds
```

---

## Common Test Cases

### Authentication Tests
- [x] Valid registration
- [x] Invalid registration (missing fields)
- [x] Duplicate email registration
- [x] Valid login
- [x] Invalid login credentials
- [x] Login with inactive account
- [x] Profile retrieval
- [x] Profile update
- [x] Password change
- [x] Token refresh
- [x] Logout

### Savings Tests
- [x] Get savings products
- [x] Get member accounts
- [x] Valid deposit
- [x] Invalid deposit (negative amount)
- [x] Valid withdrawal
- [x] Withdrawal exceeding balance
- [x] Withdrawal below minimum balance
- [x] Transaction history retrieval
- [x] Transaction filtering

### Authorization Tests
- [x] Access protected endpoint without token
- [x] Access with invalid token
- [x] Access with expired token
- [x] Member accessing admin endpoints
- [x] Admin accessing member data
- [x] Role-based permission validation

---

## Performance Testing

### Load Testing with Apache Bench
```bash
# Test login endpoint
ab -n 100 -c 10 -p login_data.json -T application/json \
   http://localhost:8000/api/auth/login

# Test protected endpoint (after getting token)
ab -n 100 -c 10 -H "Authorization: Bearer $token" \
   http://localhost:8000/api/savings/accounts
```

### Test Data File (login_data.json)
```json
{"email":"admin@sacco.com","password":"password123"}
```

---

## Common Issues

### Issue 1: CORS Errors
**Problem**: Browser requests blocked by CORS policy

**Solution**: Configure CORS in `config/cors.php`
```php
'allowed_origins' => ['http://localhost:3000'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

### Issue 2: Token Expiration
**Problem**: 401 Unauthorized after some time

**Solution**: Use refresh token endpoint
```bash
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Authorization: Bearer $expired_token"
```

### Issue 3: Validation Errors
**Problem**: 422 Unprocessable Entity

**Solution**: Check validation rules in API documentation
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Issue 4: Database Connection
**Problem**: 500 Internal Server Error

**Solution**: Check database configuration
```bash
# Verify database exists
ls -la database/database.sqlite

# Run migrations if needed
php artisan migrate:fresh --seed --seeder=SaccoDataSeeder
```

### Issue 5: Route Not Found
**Problem**: 404 Not Found for valid endpoints

**Solution**: Check route registration
```bash
# List all routes
php artisan route:list | grep api
```

---

## Test Data Management

### Reset Test Data
```bash
# Reset database with fresh test data
php artisan migrate:fresh --seed --seeder=SaccoDataSeeder
```

### Create Additional Test Users
```bash
# Use tinker to create test data
php artisan tinker

# Create test member
$user = User::create([
    'name' => 'Test Member',
    'email' => 'test@example.com',
    'password' => Hash::make('password123'),
    'member_number' => 'TEST001',
    'role' => 'member',
    'status' => 'active',
    'phone' => '+1234567890',
    'national_id' => 'TEST123456',
    'date_of_birth' => '1990-01-01',
    'gender' => 'male',
    'address' => 'Test Address',
    'occupation' => 'Tester',
    'monthly_income' => 25000,
    'membership_date' => now(),
    'approved_at' => now(),
]);
```

---

## Monitoring and Logging

### Enable Query Logging
```php
// In AppServiceProvider boot method
DB::listen(function ($query) {
    Log::info('SQL Query:', [
        'sql' => $query->sql,
        'bindings' => $query->bindings,
        'time' => $query->time
    ]);
});
```

### View Logs
```bash
# Watch log file
tail -f storage/logs/laravel.log

# View API requests
grep "api/" storage/logs/laravel.log
```

---

## Security Testing

### Test SQL Injection
```bash
# Try SQL injection in parameters
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@sacco.com",
    "password": "'; DROP TABLE users; --"
  }'
```

### Test XSS
```bash
# Try script injection
curl -X PUT http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer $token" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<script>alert('XSS')</script>"
  }'
```

### Test Rate Limiting
```bash
# Make multiple rapid requests
for i in {1..70}; do
  curl -X GET http://localhost:8000/api/savings/products
done
```

---

This testing guide provides comprehensive coverage for validating the SACCO API functionality, security, and performance. Use these tests to ensure the API works correctly before deploying to production.