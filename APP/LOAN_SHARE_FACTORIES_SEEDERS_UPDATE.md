# ✅ Loan & Share Factories/Seeders - Updated for Foreign Keys

## 🎯 **What Was Updated**

All factories and seeders that create **Loan** and **Share** entity records have been updated to properly link them to their parent **LoanAccount** and **ShareAccount** using the new foreign key fields.

---

## 📦 **Updated Files**

### 1. ✅ LoanFactory.php
**Added:**
- `loan_account_id` field (nullable by default)
- `forLoanAccount($loanAccount)` state - Link loan to a loan account
- `pending()` state - Create pending loan
- `disbursed()` state - Create disbursed loan
- `completed()` state - Create fully paid loan

**Usage:**
```php
// Create standalone loan (no account link)
$loan = Loan::factory()->create();

// Link loan to specific loan account
$loan = Loan::factory()
    ->forLoanAccount($loanAccount)
    ->create();

// Create with different statuses
$loan = Loan::factory()->pending()->create();
$loan = Loan::factory()->disbursed()->create();
$loan = Loan::factory()->completed()->create();

// Manual linking
$loan = Loan::factory()->create([
    'member_id' => $member->id,
    'loan_account_id' => $loanAccount->id,
]);
```

---

### 2. ✅ ShareFactory.php
**Added:**
- `share_account_id` field (nullable by default)
- `forShareAccount($shareAccount)` state - Link share to a share account
- `withShares($count)` state - Create certificate with specific number of shares

**Usage:**
```php
// Create standalone share certificate (no account link)
$share = Share::factory()->create();

// Link share to specific share account
$share = Share::factory()
    ->forShareAccount($shareAccount)
    ->create();

// Create with specific share count
$share = Share::factory()
    ->withShares(50)
    ->create();

// Manual linking
$share = Share::factory()->create([
    'member_id' => $member->id,
    'share_account_id' => $shareAccount->id,
    'shares_count' => 25,
]);
```

---

### 3. ✅ LoanSeeder.php
**Complete Rewrite:**

**Before:**
```php
// Only created loans (no accounts)
Loan::factory()->count(rand(0, 2))->create(['member_id' => $member->id]);
```

**After:**
```php
// Creates LoanAccount + Account + Loans + Updates aggregates
1. Create LoanAccount (fresh state)
2. Create polymorphic Account link
3. Create 0-3 individual Loans (linked to LoanAccount)
4. Update LoanAccount aggregates:
   - total_disbursed_amount
   - total_repaid_amount
   - current_outstanding
```

**Output:**
```
Seeding loans with loan accounts...
  Created loan account for John Doe with 2 loans
  Created loan account for Jane Smith with 3 loans
✅ Loan seeding completed!
Total loan accounts: 12
Total individual loans: 28
```

---

### 4. ✅ MemberSeeder.php
**Updated 3 sections:**

**Section 1: Main members (20 users)**
```php
// Before
Loan::factory()->count(2)->create(['member_id' => $user->id]);
Share::factory()->count(3)->create(['member_id' => $user->id]);

// After
// Create LoanAccount + Account + 2 Loans (linked)
// Create ShareAccount + Account + 3 Share certificates (linked)
```

**Section 2: Medium members (15 users)**
```php
// Before
Loan::factory()->count(1)->create(['member_id' => $user->id]);
Share::factory()->count(2)->create(['member_id' => $user->id]);

// After
// Create LoanAccount + Account + 1 Loan (linked)
// Create ShareAccount + Account + 2 Share certificates (linked)
```

**Section 3: Minimal members (5 users)**
```php
// Before
Share::factory()->count(1)->create(['member_id' => $user->id]);

// After
// Create ShareAccount + Account + 1 Share certificate (linked)
```

---

## 🔑 **Key Changes Summary**

### LoanFactory
| Field | Before | After |
|-------|--------|-------|
| `loan_account_id` | ❌ Missing | ✅ `null` (can be set) |
| States | ❌ None | ✅ `forLoanAccount()`, `pending()`, `disbursed()`, `completed()` |

### ShareFactory
| Field | Before | After |
|-------|--------|-------|
| `share_account_id` | ❌ Missing | ✅ `null` (can be set) |
| States | ✅ `active()`, `transferred()`, `redeemed()` | ✅ Added `forShareAccount()`, `withShares()` |

### LoanSeeder
| Aspect | Before | After |
|--------|--------|-------|
| Creates | ❌ Only Loans | ✅ LoanAccount + Account + Loans |
| Links | ❌ No linking | ✅ Loans linked via `loan_account_id` |
| Aggregates | ❌ Not calculated | ✅ Calculated and updated |

### MemberSeeder
| Aspect | Before | After |
|--------|--------|-------|
| Loans | ❌ No account | ✅ LoanAccount + linked Loans |
| Shares | ❌ No account | ✅ ShareAccount + linked Shares |
| Account records | ❌ Missing | ✅ Polymorphic Account created |

---

## 💡 **Migration Compatibility**

These updates work with the new migration files:
- `2025_10_22_180001_add_loan_account_id_to_loans_table.php`
- `2025_10_22_180002_add_share_account_id_to_shares_table.php`

**Before running seeders:**
```bash
# Run all migrations first
php artisan migrate
```

**Then seed:**
```bash
# Use any seeder
php artisan db:seed --class=LoanSeeder
php artisan db:seed --class=MemberSeeder
php artisan db:seed --class=PolymorphicAccountSeeder
```

---

## 🧪 **Testing Examples**

### Test Loan Creation with Account
```php
public function test_loan_can_be_linked_to_loan_account()
{
    $member = User::factory()->create();
    $loanAccount = LoanAccount::factory()->fresh()->create();
    
    $account = Account::factory()
        ->for($member)
        ->withLoanAccount($loanAccount)
        ->create();
    
    $loan = Loan::factory()
        ->forLoanAccount($loanAccount)
        ->create(['member_id' => $member->id]);
    
    $this->assertEquals($loanAccount->id, $loan->loan_account_id);
    $this->assertTrue($loan->loanAccount->is($loanAccount));
}
```

### Test Share Creation with Account
```php
public function test_share_can_be_linked_to_share_account()
{
    $member = User::factory()->create();
    $shareAccount = ShareAccount::factory()->fresh()->create();
    
    $account = Account::factory()
        ->for($member)
        ->withShareAccount($shareAccount)
        ->create();
    
    $share = Share::factory()
        ->forShareAccount($shareAccount)
        ->withShares(50)
        ->create(['member_id' => $member->id]);
    
    $this->assertEquals($shareAccount->id, $share->share_account_id);
    $this->assertEquals(50, $share->shares_count);
    $this->assertTrue($share->shareAccount->is($shareAccount));
}
```

### Test LoanSeeder Creates Proper Structure
```php
public function test_loan_seeder_creates_accounts_and_loans()
{
    $member = User::factory()->create(['role' => 'member']);
    
    // Run seeder logic
    $this->seed(LoanSeeder::class);
    
    // Check structures created
    $loanAccounts = Account::where('accountable_type', LoanAccount::class)->count();
    $loans = Loan::whereNotNull('loan_account_id')->count();
    
    $this->assertGreaterThan(0, $loanAccounts);
    $this->assertGreaterThan(0, $loans);
}
```

---

## 📊 **Before vs After Comparison**

### Before (Incorrect)
```php
// Loan created WITHOUT account
Loan::factory()->create([
    'member_id' => $member->id,
    'loan_account_id' => null,  // ❌ No link
]);

// Share created WITHOUT account
Share::factory()->create([
    'member_id' => $member->id,
    'share_account_id' => null,  // ❌ No link
]);
```

### After (Correct)
```php
// Step 1: Create account structure
$loanAccount = LoanAccount::factory()->fresh()->create();
Account::create([
    'member_id' => $member->id,
    'accountable_type' => LoanAccount::class,
    'accountable_id' => $loanAccount->id,
]);

// Step 2: Create loan linked to account
Loan::factory()->create([
    'member_id' => $member->id,
    'loan_account_id' => $loanAccount->id,  // ✅ Properly linked
]);

// Same for shares
$shareAccount = ShareAccount::factory()->fresh()->create();
Account::create([
    'member_id' => $member->id,
    'accountable_type' => ShareAccount::class,
    'accountable_id' => $shareAccount->id,
]);

Share::factory()->create([
    'member_id' => $member->id,
    'share_account_id' => $shareAccount->id,  // ✅ Properly linked
]);
```

---

## ✅ **Verification Checklist**

After running migrations and seeders:

```sql
-- 1. Check all loans have loan_account_id
SELECT COUNT(*) as total, 
       COUNT(loan_account_id) as linked 
FROM loans;
-- Should be: total = linked

-- 2. Check all shares have share_account_id
SELECT COUNT(*) as total, 
       COUNT(share_account_id) as linked 
FROM shares;
-- Should be: total = linked

-- 3. Verify loan accounts have related loans
SELECT la.id, COUNT(l.id) as loan_count 
FROM loan_accounts la
LEFT JOIN loans l ON l.loan_account_id = la.id
GROUP BY la.id;

-- 4. Verify share accounts have related certificates
SELECT sa.id, COUNT(s.id) as certificate_count 
FROM share_accounts sa
LEFT JOIN shares s ON s.share_account_id = sa.id
GROUP BY sa.id;
```

---

## 🎯 **Summary**

✅ **LoanFactory** - Added `loan_account_id` + helper states  
✅ **ShareFactory** - Added `share_account_id` + helper states  
✅ **LoanSeeder** - Complete rewrite to create proper account structure  
✅ **MemberSeeder** - Updated all 3 sections to link loans and shares  
✅ **PolymorphicAccountSeeder** - Already updated (previous work)

**All Loan and Share entity creation now properly creates and links to their respective account types!** 🚀
