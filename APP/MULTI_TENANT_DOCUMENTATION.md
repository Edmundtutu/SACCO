# Multi-Tenant SACCO System - Implementation Documentation

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Core Components](#core-components)
3. [Security Model](#security-model)
4. [Database Schema](#database-schema)
5. [Tenant Resolution](#tenant-resolution)
6. [API Changes](#api-changes)
7. [Data Migration](#data-migration)
8. [Testing](#testing)
9. [Deployment](#deployment)

---

## Architecture Overview

### Design Principles

This system implements **row-level multi-tenancy** with the following core principles:

1. **Single Shared Database**: All tenants share one database
2. **Row-Level Isolation**: Every tenant-scoped table has a `tenant_id` column
3. **NO Domain/Subdomain Resolution**: Tenant identification is ONLY through JWT tokens
4. **Global Query Scopes**: Automatic tenant filtering on all queries
5. **Zero Configuration**: Developers don't need to manually add tenant filtering

### Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     API Request                              │
│  (JWT Token with tenant_id in claims)                       │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              TenantMiddleware                                │
│  - Extract tenant_id from JWT                                │
│  - Validate tenant status                                    │
│  - Set tenant context                                        │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│            Application Layer                                 │
│  - All models use BelongsToTenant trait                     │
│  - Global scopes auto-filter by tenant_id                   │
│  - tenant_id automatically set on creation                   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│            Database Layer                                    │
│  ┌──────────────────────────────────────────────────┐       │
│  │  users (tenant_id, email, ...)                   │       │
│  │  UNIQUE (tenant_id, email)                       │       │
│  └──────────────────────────────────────────────────┘       │
│  ┌──────────────────────────────────────────────────┐       │
│  │  accounts (tenant_id, account_number, ...)       │       │
│  │  UNIQUE (tenant_id, account_number)              │       │
│  └──────────────────────────────────────────────────┘       │
│  ┌──────────────────────────────────────────────────┐       │
│  │  transactions (tenant_id, ...)                   │       │
│  │  INDEX (tenant_id)                               │       │
│  └──────────────────────────────────────────────────┘       │
└─────────────────────────────────────────────────────────────┘
```

---

## Core Components

### 1. Tenant Model

**Location:** `app/Models/Tenant.php`

**Key Features:**
- Stores SACCO information (name, code, slug, contact details)
- Manages subscription and status
- Enforces limits (max_members, max_staff, max_loans)
- Feature flags for tenant-specific functionality

**Key Methods:**
```php
$tenant->canOperate()              // Check if tenant is active/operational
$tenant->hasFeature('loans')       // Check if feature is enabled
$tenant->hasReachedMemberLimit()   // Check member limit
```

### 2. BelongsToTenant Trait

**Location:** `app/Traits/BelongsToTenant.php`

**Purpose:** Automatically handles tenant scoping for all models

**What it does:**
1. Auto-sets `tenant_id` when creating records
2. Applies global scope to filter all queries by tenant
3. Provides helper scopes for bypassing or changing tenant

**Usage:**
```php
class Loan extends Model
{
    use BelongsToTenant;  // ← Just add this trait
    
    // tenant_id is automatically handled!
}
```

**Available Scopes:**
```php
// Normal query - only sees current tenant's data
$loans = Loan::all();

// Bypass tenant scope (super-admin only)
$allLoans = Loan::withoutTenantScope()->get();

// Query specific tenant
$tenantLoans = Loan::forTenant($tenantId)->get();
```

### 3. TenantContext Service

**Location:** `app/Services/TenantContext.php`

**Purpose:** Manages current tenant in application context

**Helper Functions:**
```php
tenant()        // Get current Tenant instance
tenantId()      // Get current tenant ID
setTenant($id)  // Set tenant context
clearTenant()   // Clear tenant context
```

### 4. TenantMiddleware

**Location:** `app/Http/Middleware/TenantMiddleware.php`

**Purpose:** Resolves and validates tenant for every request

**Tenant Resolution Strategy:**

#### Priority 1: Authenticated User (PRIMARY)
```php
// User's tenant_id from database
if ($user && $user->tenant_id) {
    $tenantId = $user->tenant_id;
}
```

#### Priority 2: X-Tenant-ID Header (CONTROLLED)
```php
// Only allowed for:
// - Super-admin users
// - Public tenant info endpoints
// - Unauthenticated onboarding flows
if ($request->header('X-Tenant-ID')) {
    if ($this->canUseHeaderTenantId($request, $user)) {
        $tenantId = $request->header('X-Tenant-ID');
    }
}
```

#### Priority 3: Request Parameter (LIMITED)
```php
// Only allowed for:
// - Registration
// - Invitations
// - Joining a SACCO
if ($request->input('tenant_id')) {
    if ($this->isAllowedTenantParameterRoute($request)) {
        $tenantId = $request->input('tenant_id');
    }
}
```

**Validation:**
- Checks if tenant exists
- Validates tenant is not suspended/inactive
- Rejects expired trial tenants
- Sets tenant in application context

---

## Security Model

### 1. Data Isolation

**Automatic Enforcement:**
```php
// This query automatically includes: WHERE tenant_id = {current_tenant}
$users = User::all();

// Cross-tenant access is BLOCKED
setTenant(1);
$user = User::find($userId);  // Only returns if user.tenant_id = 1
```

### 2. Mass Assignment Protection

**tenant_id is NEVER mass-assignable:**
```php
protected $guarded = ['tenant_id'];

// This will NOT change tenant_id
User::create([
    'tenant_id' => 999,  // ← Ignored
    'name' => 'John',
    'email' => 'john@example.com',
]);
// tenant_id is set automatically by BelongsToTenant trait
```

### 3. JWT Token Security

**Token Payload:**
```json
{
  "sub": 1,                    // User ID
  "tenant_id": 5,              // Tenant ID (immutable)
  "role": "member",
  "status": "active",
  "iat": 1674567890,
  "exp": 1674571490
}
```

**Security Features:**
- `tenant_id` is persisted across token refreshes
- Cannot be modified without re-authentication
- Validated on every request

### 4. Super Admin Bypass

**Controlled Bypass:**
```php
if (isSuperAdmin() && canBypassTenantScope()) {
    // Can query across tenants
    $allUsers = User::withoutTenantScope()->get();
}
```

**Requirements:**
- User must have `role = 'super_admin'` OR `is_super_admin = true`
- Must be running in console (for safety)

---

## Database Schema

### Tenant-Scoped Tables

All tables with `tenant_id`:

| Table | Unique Constraints |
|-------|-------------------|
| users | (tenant_id, email) |
| individual_profiles | (tenant_id, national_id) |
| vsla_profiles | (tenant_id, registration_number) |
| mfi_profiles | (tenant_id, license_number) |
| savings_products | (tenant_id, code) |
| loan_products | (tenant_id, code) |
| accounts | (tenant_id, account_number) |
| loans | (tenant_id, loan_number) |
| transactions | (tenant_id, transaction_number) |
| chart_of_accounts | (tenant_id, account_code) |
| memberships | (tenant_id) - indexed |
| savings_accounts | (tenant_id) - indexed |
| loan_accounts | (tenant_id) - indexed |
| share_accounts | (tenant_id) - indexed |
| loan_guarantors | (tenant_id) - indexed |
| loan_repayments | (tenant_id) - indexed |
| shares | (tenant_id) - indexed |
| savings_goals | (tenant_id) - indexed |
| general_ledger | (tenant_id) - indexed |
| dividends | (tenant_id) - indexed |
| dividend_payments | (tenant_id) - indexed |

### Migration Files

Located in `database/migrations/`:
- `2026_01_22_000001_create_tenants_table.php`
- `2026_01_22_000002_add_tenant_id_to_users_table.php`
- `2026_01_22_000003_add_tenant_id_to_membership_tables.php`
- `2026_01_22_000004_add_tenant_id_to_product_tables.php`
- `2026_01_22_000005_add_tenant_id_to_account_tables.php`
- `2026_01_22_000006_add_tenant_id_to_loan_tables.php`
- `2026_01_22_000007_add_tenant_id_to_transaction_tables.php`
- `2026_01_22_000008_add_tenant_id_to_accounting_tables.php`

---

## Tenant Resolution

### Request Flow

```
1. Request arrives with JWT token
   ↓
2. TenantMiddleware extracts tenant_id from JWT
   ↓
3. Load Tenant from database
   ↓
4. Validate tenant.canOperate()
   ↓
5. setTenant($tenant)
   ↓
6. All queries auto-filtered by tenant_id
   ↓
7. Response sent
   ↓
8. clearTenant() on next request
```

### Error Responses

**No Tenant Context:**
```json
HTTP 400
{
  "success": false,
  "message": "Tenant context could not be resolved"
}
```

**Invalid Tenant:**
```json
HTTP 404
{
  "success": false,
  "message": "Invalid tenant"
}
```

**Suspended Tenant:**
```json
HTTP 403
{
  "success": false,
  "message": "Tenant is suspended. Please contact support.",
  "tenant_status": "suspended"
}
```

---

## API Changes

### Registration Endpoint

**Old:**
```http
POST /api/auth/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  ...
}
```

**New:**
```http
POST /api/auth/register
{
  "tenant_id": 1,               ← NEW: Required
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  ...
}
```

### Login Response

**Old:**
```json
{
  "user": {...},
  "token": "..."
}
```

**New:**
```json
{
  "user": {...},
  "tenant": {                   ← NEW
    "id": 1,
    "name": "Kilimo SACCO",
    "code": "SAC000001",
    "status": "active"
  },
  "token": "..."
}
```

### All Other Endpoints

**NO CHANGES REQUIRED** - Tenant scoping is automatic!

Just ensure requests include JWT token:
```http
GET /api/accounts
Authorization: Bearer {token}
```

---

## Data Migration

### Step 1: Run Migrations

```bash
php artisan migrate
```

This creates:
- `tenants` table
- Adds `tenant_id` to all tenant-scoped tables
- Updates unique constraints

### Step 2: Seed Default Tenant

```bash
php artisan db:seed --class=TenantDataMigrationSeeder
```

This:
- Creates a default "Main SACCO" tenant
- Backfills `tenant_id` to ALL existing records
- Assigns all data to the default tenant

### Step 3: Verify Migration

```bash
php artisan tinker

# Check default tenant
$tenant = \App\Models\Tenant::first();
echo $tenant->sacco_name;  // "Main SACCO"

# Verify data association
setTenant($tenant);
$users = \App\Models\User::count();
echo "Users in tenant: $users";
```

### Step 4: Create Additional Tenants

```php
$newTenant = Tenant::create([
    'sacco_name' => 'Farmers SACCO',
    'sacco_code' => 'SAC000002',
    'slug' => 'farmers-sacco',
    'email' => 'admin@farmers.com',
    'status' => 'active',
    'subscription_plan' => 'standard',
    'max_members' => 500,
]);
```

---

## Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run tenant isolation tests only
php artisan test --filter TenantIsolationTest
```

### Key Test Scenarios

1. **Tenant Isolation**
   - Tenant A cannot see Tenant B's data
   - Global scopes work correctly
   - Cross-tenant access is blocked

2. **JWT Integration**
   - Token includes tenant_id
   - Login sets correct tenant
   - Token refresh preserves tenant

3. **Security**
   - tenant_id cannot be mass-assigned
   - Suspended tenants cannot login
   - Header spoofing is prevented

4. **Data Uniqueness**
   - Email unique per tenant
   - Account numbers unique per tenant
   - Transaction numbers unique per tenant

---

## Deployment

### Pre-Deployment Checklist

- [ ] Database backup completed
- [ ] All migrations tested in staging
- [ ] Seeder tested with existing data
- [ ] API tests pass
- [ ] PWA integration guide reviewed
- [ ] JWT tokens include tenant_id
- [ ] Middleware registered in Kernel
- [ ] TenantServiceProvider registered

### Deployment Steps

1. **Backup Database**
   ```bash
   php artisan db:backup  # or your backup method
   ```

2. **Deploy Code**
   ```bash
   git pull origin main
   composer install --no-dev
   ```

3. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

4. **Seed Default Tenant**
   ```bash
   php artisan db:seed --class=TenantDataMigrationSeeder --force
   ```

5. **Clear Caches**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

6. **Verify**
   ```bash
   # Test API endpoint
   curl -X POST https://api.yourdomain.com/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"password"}'
   ```

### Post-Deployment

1. **Monitor Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check Tenant Resolution**
   - Test login for different users
   - Verify tenant information in responses
   - Confirm data isolation

3. **Update PWA**
   - Deploy PWA with tenant support
   - Force users to re-login for new tokens

---

## Troubleshooting

### Issue: "Tenant context could not be resolved"

**Cause:** User's token doesn't have tenant_id or middleware can't find tenant

**Solution:**
1. Check user has `tenant_id` in database
2. Verify JWT includes `tenant_id` in payload
3. Ensure TenantMiddleware is applied to route

### Issue: "No query results for model [Tenant]"

**Cause:** tenant_id in token references non-existent tenant

**Solution:**
1. Verify tenant exists: `Tenant::find($id)`
2. Check tenant wasn't soft-deleted
3. User may need to re-register

### Issue: Queries returning empty results

**Cause:** Global scope filtering by tenant

**Solution:**
1. Verify tenant context is set: `tenantId()`
2. Check user's tenant_id matches data's tenant_id
3. Use `withoutTenantScope()` to debug

### Issue: Can't create records

**Cause:** tenant_id not being set automatically

**Solution:**
1. Ensure model uses `BelongsToTenant` trait
2. Check `tenantId()` returns value
3. Verify TenantServiceProvider is registered

---

## Best Practices

1. **Always Use Traits**
   - Never manually add `WHERE tenant_id = ?` clauses
   - Let the trait handle scoping

2. **Never Trust Headers**
   - Don't accept `X-Tenant-ID` from untrusted sources
   - Always validate tenant ownership

3. **Test Isolation**
   - Write tests that create multiple tenants
   - Verify cross-tenant access is blocked

4. **Monitor Limits**
   - Check tenant limits before creating resources
   - Provide clear error messages

5. **Soft Deletes**
   - Tenant model uses soft deletes
   - Deleted tenants' data remains but is inaccessible

---

## Support

For questions or issues:
1. Check this documentation first
2. Review test files for examples
3. Check API documentation in `openapi.yaml`
4. Review code comments in core files

## Conclusion

This multi-tenant architecture provides:
- ✅ Strong data isolation
- ✅ Automatic tenant scoping
- ✅ Security by default
- ✅ Zero configuration for developers
- ✅ JWT-based tenant resolution (NO domains!)
- ✅ Production-ready

The system is designed for **financial applications** where data security and isolation are critical.
