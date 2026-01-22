<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Loan;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant1;
    protected Tenant $tenant2;
    protected User $user1;
    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two separate tenants
        $this->tenant1 = Tenant::create([
            'sacco_code' => 'SAC000001',
            'sacco_name' => 'Tenant One SACCO',
            'slug' => 'tenant-one',
            'email' => 'tenant1@example.com',
            'status' => 'active',
        ]);

        $this->tenant2 = Tenant::create([
            'sacco_code' => 'SAC000002',
            'sacco_name' => 'Tenant Two SACCO',
            'slug' => 'tenant-two',
            'email' => 'tenant2@example.com',
            'status' => 'active',
        ]);

        // Create users for each tenant
        $this->user1 = User::create([
            'tenant_id' => $this->tenant1->id,
            'name' => 'User One',
            'email' => 'user1@tenant1.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'status' => 'active',
        ]);

        $this->user2 = User::create([
            'tenant_id' => $this->tenant2->id,
            'name' => 'User Two',
            'email' => 'user2@tenant2.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function tenant_can_only_see_their_own_users()
    {
        // Set tenant1 context
        setTenant($this->tenant1);

        // Query users - should only see tenant1 users
        $users = User::all();

        $this->assertCount(1, $users);
        $this->assertEquals($this->user1->id, $users->first()->id);
        $this->assertEquals($this->tenant1->id, $users->first()->tenant_id);
    }

    /** @test */
    public function tenant_cannot_access_another_tenants_users()
    {
        // Set tenant1 context
        setTenant($this->tenant1);

        // Try to find user2 (from tenant2)
        $user = User::find($this->user2->id);

        $this->assertNull($user);
    }

    /** @test */
    public function tenant_scope_is_automatically_applied_to_queries()
    {
        // Create accounts for both tenants
        $account1 = Account::create([
            'tenant_id' => $this->tenant1->id,
            'account_number' => 'ACC001',
            'member_id' => $this->user1->id,
            'accountable_type' => 'App\Models\SavingsAccount',
            'accountable_id' => 1,
            'status' => 'active',
        ]);

        $account2 = Account::create([
            'tenant_id' => $this->tenant2->id,
            'account_number' => 'ACC002',
            'member_id' => $this->user2->id,
            'accountable_type' => 'App\Models\SavingsAccount',
            'accountable_id' => 2,
            'status' => 'active',
        ]);

        // Set tenant1 context
        setTenant($this->tenant1);

        $accounts = Account::all();

        $this->assertCount(1, $accounts);
        $this->assertEquals($account1->id, $accounts->first()->id);
    }

    /** @test */
    public function jwt_token_includes_tenant_id()
    {
        $token = JWTAuth::fromUser($this->user1);
        $payload = JWTAuth::setToken($token)->getPayload();

        $this->assertEquals($this->tenant1->id, $payload->get('tenant_id'));
        $this->assertEquals($this->user1->role, $payload->get('role'));
    }

    /** @test */
    public function login_sets_correct_tenant_context()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user1@tenant1.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'tenant' => [
                    'id',
                    'name',
                    'code',
                    'status',
                ],
                'token',
            ],
        ]);

        $this->assertEquals($this->tenant1->id, $response->json('data.tenant.id'));
        $this->assertEquals($this->tenant1->sacco_name, $response->json('data.tenant.name'));
    }

    /** @test */
    public function suspended_tenant_cannot_login()
    {
        // Suspend tenant1
        $this->tenant1->update(['status' => 'suspended']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user1@tenant1.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Your SACCO is currently suspended. Please contact support.']);
    }

    /** @test */
    public function cross_tenant_data_access_is_blocked()
    {
        // Create transaction for tenant1
        $transaction1 = Transaction::create([
            'tenant_id' => $this->tenant1->id,
            'transaction_number' => 'TXN001',
            'member_id' => $this->user1->id,
            'account_id' => 1,
            'type' => 'deposit',
            'amount' => 1000,
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        // Set tenant2 context
        setTenant($this->tenant2);

        // Try to find tenant1's transaction
        $transaction = Transaction::find($transaction1->id);

        $this->assertNull($transaction);
    }

    /** @test */
    public function tenant_id_cannot_be_mass_assigned()
    {
        setTenant($this->tenant1);

        // Attempt to create a user with a different tenant_id via mass assignment
        $user = User::create([
            'tenant_id' => $this->tenant2->id, // Try to set wrong tenant
            'name' => 'Malicious User',
            'email' => 'malicious@example.com',
            'password' => bcrypt('password'),
            'role' => 'member',
            'status' => 'active',
        ]);

        // Verify that tenant_id was set by the trait, not by mass assignment
        $this->assertEquals($this->tenant1->id, $user->tenant_id);
        $this->assertNotEquals($this->tenant2->id, $user->tenant_id);
    }

    /** @test */
    public function without_tenant_scope_can_see_all_records()
    {
        setTenant($this->tenant1);

        // Without scope, we should see all users
        $allUsers = User::withoutTenantScope()->get();

        $this->assertCount(2, $allUsers);
    }

    /** @test */
    public function for_tenant_scope_can_query_specific_tenant()
    {
        setTenant($this->tenant1);

        // Query tenant2's users specifically
        $tenant2Users = User::forTenant($this->tenant2->id)->get();

        $this->assertCount(1, $tenant2Users);
        $this->assertEquals($this->user2->id, $tenant2Users->first()->id);
    }

    /** @test */
    public function registration_requires_valid_tenant()
    {
        $response = $this->postJson('/api/auth/register', [
            'tenant_id' => $this->tenant1->id,
            'name' => 'New User',
            'email' => 'newuser@tenant1.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+254700000000',
            'national_id' => '12345678',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Test Address',
            'occupation' => 'Developer',
            'monthly_income' => 50000,
            'next_of_kin_name' => 'Next of Kin',
            'next_of_kin_relationship' => 'Spouse',
            'next_of_kin_phone' => '+254700000001',
            'next_of_kin_address' => 'Kin Address',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'Registration successful. Your membership is pending approval.',
        ]);

        // Verify user was created with correct tenant
        $user = User::withoutTenantScope()
            ->where('email', 'newuser@tenant1.com')
            ->first();

        $this->assertNotNull($user);
        $this->assertEquals($this->tenant1->id, $user->tenant_id);
    }

    /** @test */
    public function email_uniqueness_is_per_tenant()
    {
        // User1 already has email 'user1@tenant1.com' in tenant1

        // Try to create another user with same email in tenant1 (should fail)
        $response1 = $this->postJson('/api/auth/register', [
            'tenant_id' => $this->tenant1->id,
            'name' => 'Duplicate User',
            'email' => 'user1@tenant1.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+254700000000',
            'national_id' => '12345679',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Test Address',
            'occupation' => 'Developer',
            'monthly_income' => 50000,
            'next_of_kin_name' => 'Next of Kin',
            'next_of_kin_relationship' => 'Spouse',
            'next_of_kin_phone' => '+254700000001',
            'next_of_kin_address' => 'Kin Address',
        ]);

        $response1->assertStatus(422);

        // Create user with same email in tenant2 (should succeed)
        $response2 = $this->postJson('/api/auth/register', [
            'tenant_id' => $this->tenant2->id,
            'name' => 'Same Email User',
            'email' => 'user1@tenant1.com', // Same email, different tenant
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+254700000000',
            'national_id' => '12345680',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Test Address',
            'occupation' => 'Developer',
            'monthly_income' => 50000,
            'next_of_kin_name' => 'Next of Kin',
            'next_of_kin_relationship' => 'Spouse',
            'next_of_kin_phone' => '+254700000001',
            'next_of_kin_address' => 'Kin Address',
        ]);

        $response2->assertStatus(201);
    }
}
