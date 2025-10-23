# ✅ Factories & Seeders - Updated for Corrected Architecture

## 🎯 **What Changed**

The factories and seeders have been completely rewritten to match the corrected architecture where:
- **Account types** (LoanAccount, ShareAccount) track aggregates
- **Entity records** (Loan, Share) are individual items linked to accounts

---

## 📦 **Updated Files**

### 1. LoanAccountFactory
**Before:** Created individual loan fields (principal, interest, etc.)  
**After:** Creates account-level tracking fields

```php
// Account-level aggregates
'total_disbursed_amount' => 2000000,  // Total across all loans
'total_repaid_amount' => 800000,      // Total repaid
'current_outstanding' => 1200000,     // Current total owed

// Account configuration
'min_loan_limit' => 10000,
'max_loan_limit' => 5000000,
'repayment_frequency_type' => 'monthly',
'linked_savings_account' => null,

// Features & audit
'account_features' => [
    'auto_deduct_from_savings' => true,
    'sms_notifications' => true,
],
```

**States Available:**
- `fresh()` - New account with no loans (all zeros)
- `withActiveLoans()` - Account with outstanding balance
- `linkedToSavings($savings)` - Link to savings account
- `highLimit()` - High loan limits

### 2. ShareAccountFactory
**Before:** Created individual certificate fields (certificate_number, etc.)  
**After:** Creates account-level tracking fields

```php
// Share ownership aggregates
'share_units' => 150,                 // Total units owned
'share_price' => 1000,                // Current price per unit
'total_share_value' => 150000,        // Total value

// Dividends tracking
'dividends_earned' => 15000,
'dividends_pending' => 5000,
'dividends_paid' => 10000,

// Account classification
'account_class' => 'ordinary',
'locked_shares' => 30,                // Shares locked as collateral
'bonus_shares_earned' => 10,
'membership_fee_paid' => true,

// Features
'account_features' => [
    'auto_reinvest_dividends' => true,
    'voting_rights' => true,
],
```

**States Available:**
- `fresh()` - New account with no shares
- `premium()` - Premium class with high shares
- `withPendingDividends()` - Has unpaid dividends
- `withLockedShares($count)` - Has locked shares

### 3. PolymorphicAccountSeeder
**Before:** Only created accounts  
**After:** Creates accounts WITH linked entities

**For each member, the seeder:**

1. **Creates Savings Accounts** (1-2 per member)
   ```php
   SavingsAccount → Account (polymorphic link)
   ```

2. **Creates Loan Account with Loans** (60% chance)
   ```php
   LoanAccount (fresh, totals = 0)
     └─ Account (polymorphic link)
     
   Create 1-3 individual Loans
     ├─ Loan #1 (status: disbursed, linked to LoanAccount)
     ├─ Loan #2 (status: active, linked to LoanAccount)
     └─ Loan #3 (status: completed, linked to LoanAccount)
     
   Update LoanAccount with aggregates
     ├─ total_disbursed_amount = sum(all loans)
     ├─ total_repaid_amount = sum(total_paid)
     └─ current_outstanding = sum(active loans)
   ```

3. **Creates Share Account with Certificates** (70% chance)
   ```php
   ShareAccount (fresh, totals = 0)
     └─ Account (polymorphic link)
     
   Create 1-4 individual Share certificates
     ├─ Certificate #1 (20 shares, purchased 2023-01)
     ├─ Certificate #2 (15 shares, purchased 2023-06)
     └─ Certificate #3 (30 shares, purchased 2024-01)
     
   Update ShareAccount with aggregates
     ├─ share_units = sum(all certificates)
     ├─ total_share_value = units × current_price
     └─ dividends calculated based on total units
   ```

---

## 📊 **Seeder Output Example**

```
Seeding polymorphic accounts with entities...
Seeding accounts for member: John Doe
  → Created loan account with 2 loans
  → Created share account with 3 certificates (65 units)
Seeding accounts for member: Jane Smith
  → Created loan account with 1 loans
  → Created share account with 2 certificates (35 units)
...

✅ Polymorphic accounts seeded successfully!

📊 Accounts breakdown:
  - Savings Accounts: 15
  - Loan Accounts: 6
  - Share Accounts: 7

📝 Entity records:
  - Individual Loans: 12
  - Share Certificates: 18
```

---

## 💡 **Usage Examples**

### Creating Test Data

```bash
# Seed polymorphic accounts with entities
php artisan db:seed --class=PolymorphicAccountSeeder
```

### Using Factories in Tests

```php
// 1. Create fresh loan account (no loans yet)
$loanAccount = LoanAccount::factory()->fresh()->create();
$account = Account::factory()
    ->withLoanAccount($loanAccount)
    ->create();

// Member can now apply for loans
$loan = Loan::factory()->create([
    'member_id' => $member->id,
    'loan_account_id' => $loanAccount->id,
]);

// 2. Create loan account with existing loans
$loanAccount = LoanAccount::factory()
    ->withActiveLoans()
    ->create();
// Already has outstanding balance

// 3. Create share account with fresh state
$shareAccount = ShareAccount::factory()->fresh()->create();
// share_units = 0, ready for first purchase

// 4. Create premium share account
$shareAccount = ShareAccount::factory()->premium()->create();
// High shares, premium class

// 5. Create account with locked shares
$shareAccount = ShareAccount::factory()
    ->withLockedShares(50)
    ->create();
// 50 shares locked (e.g., as loan collateral)
```

### Testing Account Aggregates

```php
public function test_loan_account_tracks_aggregates()
{
    $member = User::factory()->create();
    $loanAccount = LoanAccount::factory()->fresh()->create();
    
    Account::factory()
        ->for($member)
        ->withLoanAccount($loanAccount)
        ->create();
    
    // Create 2 loans
    $loan1 = Loan::factory()->create([
        'member_id' => $member->id,
        'loan_account_id' => $loanAccount->id,
        'principal_amount' => 100000,
        'outstanding_balance' => 100000,
        'total_paid' => 0,
    ]);
    
    $loan2 = Loan::factory()->create([
        'member_id' => $member->id,
        'loan_account_id' => $loanAccount->id,
        'principal_amount' => 200000,
        'outstanding_balance' => 150000,
        'total_paid' => 50000,
    ]);
    
    // Update aggregates
    $loanAccount->update([
        'total_disbursed_amount' => 300000,
        'total_repaid_amount' => 50000,
        'current_outstanding' => 250000,
    ]);
    
    // Assertions
    $this->assertEquals(300000, $loanAccount->total_disbursed_amount);
    $this->assertEquals(2, $loanAccount->loans->count());
}

public function test_share_account_tracks_certificates()
{
    $member = User::factory()->create();
    $shareAccount = ShareAccount::factory()->fresh()->create([
        'share_price' => 1000,
    ]);
    
    Account::factory()
        ->for($member)
        ->withShareAccount($shareAccount)
        ->create();
    
    // Purchase shares over time (3 certificates)
    $cert1 = Share::factory()->create([
        'member_id' => $member->id,
        'share_account_id' => $shareAccount->id,
        'shares_count' => 20,
    ]);
    
    $cert2 = Share::factory()->create([
        'member_id' => $member->id,
        'share_account_id' => $shareAccount->id,
        'shares_count' => 30,
    ]);
    
    // Update aggregates
    $totalUnits = $shareAccount->shares->sum('shares_count');
    $shareAccount->update([
        'share_units' => $totalUnits,
        'total_share_value' => $totalUnits * 1000,
    ]);
    
    // Assertions
    $this->assertEquals(50, $shareAccount->share_units);
    $this->assertEquals(50000, $shareAccount->total_share_value);
    $this->assertEquals(2, $shareAccount->shares->count());
}
```

---

## 🔑 **Key Differences**

### Before (Incorrect)
```php
// LoanAccountFactory created loan details
LoanAccount::factory()->create([
    'principal_amount' => 100000,      // ❌ Wrong!
    'interest_rate' => 12.5,           // ❌ Wrong!
    'monthly_payment' => 5000,         // ❌ Wrong!
]);

// ShareAccountFactory created certificate details
ShareAccount::factory()->create([
    'certificate_number' => 'CERT001',  // ❌ Wrong!
    'shares_count' => 50,               // ❌ Wrong!
    'purchase_date' => now(),           // ❌ Wrong!
]);
```

### After (Correct)
```php
// LoanAccountFactory creates aggregate tracking
LoanAccount::factory()->create([
    'total_disbursed_amount' => 500000,  // ✅ Aggregate
    'total_repaid_amount' => 200000,     // ✅ Aggregate
    'current_outstanding' => 300000,     // ✅ Aggregate
    'max_loan_limit' => 1000000,         // ✅ Account limit
]);

// Individual loans are separate
Loan::factory()->create([
    'loan_account_id' => $loanAccount->id,  // ✅ Linked
    'principal_amount' => 100000,           // ✅ Individual loan
    'interest_rate' => 12.5,                // ✅ Individual loan
]);

// ShareAccountFactory creates aggregate tracking
ShareAccount::factory()->create([
    'share_units' => 150,                // ✅ Total units
    'total_share_value' => 150000,       // ✅ Total value
    'dividends_earned' => 15000,         // ✅ Total dividends
]);

// Individual certificates are separate
Share::factory()->create([
    'share_account_id' => $shareAccount->id,  // ✅ Linked
    'certificate_number' => 'CERT001',        // ✅ Individual cert
    'shares_count' => 50,                     // ✅ Individual cert
]);
```

---

## ✅ **Summary**

All factories and seeders have been updated to properly:

1. ✅ Create **account-level tracking** (aggregates, limits, settings)
2. ✅ Create **individual entities** (loans, share certificates)
3. ✅ Link entities to their parent accounts
4. ✅ Calculate and update account aggregates
5. ✅ Provide useful factory states for testing
6. ✅ Generate comprehensive test data

**The factories and seeders are production-ready!** 🚀
