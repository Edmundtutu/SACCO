# ✅ Corrected Polymorphic Account Architecture

## 🎯 **Key Concept: Account Types vs Entity Records**

### The Critical Distinction

**Account Types** (polymorphic accountable):
- Track **aggregate/summary data** for a member
- One per member per account type
- Fields: totals, limits, settings, status

**Entity Records** (separate tables):
- Track **individual transactions/certificates**
- Multiple records per account
- Fields: specific details, amounts, dates

---

## 📊 **Complete Architecture**

```
┌────────────────────────────────────────────────────────────────┐
│                    ACCOUNTS (Polymorphic Hub)                  │
│  id, account_number, member_id, accountable_type,             │
│  accountable_id, status, opening_date, last_transaction_date  │
└────────────┬───────────────────────────────────────────────────┘
             │
             ├─ morphTo ──→ SAVINGS_ACCOUNTS
             │              - balance, available_balance
             │              - interest_rate, interest_earned
             │              - minimum_balance, overdraft_limit
             │
             ├─ morphTo ──→ LOAN_ACCOUNTS
             │              - total_disbursed_amount
             │              - total_repaid_amount
             │              - current_outstanding
             │              - linked_savings_account
             │              - min/max_loan_limit
             │              - repayment_frequency_type
             │
             └─ morphTo ──→ SHARE_ACCOUNTS
                            - share_units (total owned)
                            - share_price (current)
                            - total_share_value
                            - dividends_earned/pending/paid
                            - account_class, locked_shares
                            - bonus_shares_earned


┌──────────────────────────────────────────────────────────────┐
│                   SEPARATE ENTITY TABLES                      │
└──────────────────────────────────────────────────────────────┘

LOANS (individual loan applications)
├─ id, loan_number, member_id, loan_account_id (NEW!)
├─ loan_product_id, principal_amount
├─ interest_rate, repayment_period_months
├─ monthly_payment, outstanding_balance
├─ status, disbursement_date, maturity_date
└─ Purpose: Track each loan application lifecycle

SHARES (individual share certificates)
├─ id, certificate_number, member_id, share_account_id (NEW!)
├─ shares_count (in this certificate)
├─ share_value, total_value, purchase_date
├─ status, transfer_details, redemption_date
└─ Purpose: Track individual share purchases/certificates
```

---

## 🔗 **Relationships**

### SavingsAccount
```php
account() → morphOne(Account) // Polymorphic account
savingsProduct() → belongsTo(SavingsProduct)
transactions() → hasMany(Transaction) // Direct account transactions
```

### LoanAccount
```php
account() → morphOne(Account) // Polymorphic account
loans() → hasMany(Loan, 'loan_account_id') // All loans under this account
activeLoans() → hasMany(Loan) // Active loans only
linkedSavingsAccount() → belongsTo(SavingsAccount)
```

### ShareAccount
```php
account() → morphOne(Account) // Polymorphic account
shares() → hasMany(Share, 'share_account_id') // All certificates
activeShares() → hasMany(Share) // Active certificates only
```

### Loan (Entity)
```php
member() → belongsTo(User)
loanAccount() → belongsTo(LoanAccount) // NEW!
loanProduct() → belongsTo(LoanProduct)
repayments() → hasMany(LoanRepayment)
guarantors() → hasMany(LoanGuarantor)
```

### Share (Entity)
```php
member() → belongsTo(User)
shareAccount() → belongsTo(ShareAccount) // NEW!
processedBy() → belongsTo(User)
```

---

## 💡 **Example Scenarios**

### Scenario 1: Member Opens a Loan Account

```php
// Step 1: Create loan account (account-level tracking)
$loanAccount = LoanAccount::create([
    'total_disbursed_amount' => 0,
    'total_repaid_amount' => 0,
    'current_outstanding' => 0,
    'min_loan_limit' => 10000,
    'max_loan_limit' => 500000,
    'repayment_frequency_type' => 'monthly',
]);

// Step 2: Create polymorphic account record
$account = Account::create([
    'account_number' => 'LN00001234',
    'member_id' => $member->id,
    'accountable_type' => LoanAccount::class,
    'accountable_id' => $loanAccount->id,
    'status' => 'active',
]);

// Now member has a loan ACCOUNT but no loans yet
```

### Scenario 2: Member Applies for a Loan

```php
// Create individual loan application
$loan = Loan::create([
    'member_id' => $member->id,
    'loan_account_id' => $loanAccount->id, // Link to loan account
    'loan_product_id' => $product->id,
    'principal_amount' => 100000,
    'interest_rate' => 12.5,
    'repayment_period_months' => 24,
    'status' => 'pending',
]);

// Loan account totals remain unchanged until disbursement
```

### Scenario 3: Loan is Disbursed

```php
// Update individual loan
$loan->update([
    'status' => 'disbursed',
    'disbursement_date' => now(),
    'outstanding_balance' => $loan->total_amount,
]);

// Update loan account aggregates
$loanAccount->recordDisbursement($loan->principal_amount);
// Now: total_disbursed_amount += 100000
//      current_outstanding += 100000
```

### Scenario 4: Member Makes a Repayment

```php
// Update individual loan
$loan->applyPayment(5000);
// Allocates to penalty → interest → principal

// Update loan account aggregates
$loanAccount->recordRepayment(5000);
// Now: total_repaid_amount += 5000
//      current_outstanding -= 5000
```

### Scenario 5: Member Buys Shares

```php
// Step 1: Ensure member has share account
if (!$member->shareAccount) {
    $shareAccount = ShareAccount::create([
        'share_units' => 0,
        'share_price' => 1000, // Current price
        'total_share_value' => 0,
        'account_class' => 'ordinary',
    ]);
    
    Account::create([
        'account_number' => 'SHR00001234',
        'member_id' => $member->id,
        'accountable_type' => ShareAccount::class,
        'accountable_id' => $shareAccount->id,
        'status' => 'active',
    ]);
}

// Step 2: Create individual share certificate
$share = Share::create([
    'member_id' => $member->id,
    'share_account_id' => $shareAccount->id, // Link to share account
    'certificate_number' => 'CERT-' . uniqid(),
    'shares_count' => 10, // Buying 10 shares
    'share_value' => 1000,
    'total_value' => 10000,
    'purchase_date' => now(),
    'status' => 'active',
]);

// Step 3: Update share account aggregates
$shareAccount->recordSharePurchase(10, 1000);
// Now: share_units += 10 (total: 10)
//      total_share_value = 10 * 1000 = 10000
```

---

## 🔍 **Querying Examples**

### Get All Member Accounts
```php
$accounts = Account::where('member_id', $memberId)
    ->with('accountable')
    ->get();

foreach ($accounts as $account) {
    if ($account->isSavingsAccount()) {
        echo "Savings Balance: " . $account->accountable->balance;
    }
    if ($account->isLoanAccount()) {
        echo "Total Disbursed: " . $account->accountable->total_disbursed_amount;
        echo "Active Loans: " . $account->accountable->activeLoans->count();
    }
    if ($account->isShareAccount()) {
        echo "Share Units: " . $account->accountable->share_units;
        echo "Certificates: " . $account->accountable->shares->count();
    }
}
```

### Get All Loans for a Member
```php
// Option 1: Through loan account
$loanAccount = Account::where('member_id', $memberId)
    ->ofType('loan')
    ->first()
    ->accountable;
    
$loans = $loanAccount->loans;

// Option 2: Direct query
$loans = Loan::where('member_id', $memberId)->get();
```

### Get Total Outstanding Across All Members
```php
$totalOutstanding = LoanAccount::sum('current_outstanding');
```

### Get Member's Total Share Value
```php
$shareAccount = Account::where('member_id', $memberId)
    ->ofType('share')
    ->first()
    ->accountable;
    
$totalValue = $shareAccount->total_share_value;
$certificates = $shareAccount->shares; // Individual certificates
```

---

## 📝 **Migration Requirements**

### Migrations Needed:
1. ✅ `create_savings_accounts_table.php`
2. ✅ `create_loan_accounts_table.php`
3. ✅ `create_share_accounts_table.php`
4. ✅ `refactor_accounts_table_to_polymorphic.php`
5. ⚠️ **ADD** `add_account_id_to_loans_table.php`
6. ⚠️ **ADD** `add_account_id_to_shares_table.php`
7. ✅ `migrate_existing_accounts_to_polymorphic.php`

### New Migrations to Create:

```php
// Migration: add_loan_account_id_to_loans_table.php
Schema::table('loans', function (Blueprint $table) {
    $table->foreignId('loan_account_id')
        ->nullable()
        ->after('member_id')
        ->constrained('loan_accounts')
        ->onDelete('cascade');
    
    $table->index('loan_account_id');
});

// Migration: add_share_account_id_to_shares_table.php
Schema::table('shares', function (Blueprint $table) {
    $table->foreignId('share_account_id')
        ->nullable()
        ->after('member_id')
        ->constrained('share_accounts')
        ->onDelete('cascade');
    
    $table->index('share_account_id');
});
```

---

## ✅ **Benefits of This Architecture**

### 1. **Clear Separation**
- Account-level data (aggregates, limits, settings)
- Entity-level data (individual transactions, certificates)

### 2. **Efficient Queries**
```php
// Get totals WITHOUT loading all loans
$totalOutstanding = $loanAccount->current_outstanding; // Single field

// vs old way (expensive)
$totalOutstanding = $member->loans()->sum('outstanding_balance'); // Aggregates all rows
```

### 3. **Historical Tracking**
- Keep all loan applications (approved, rejected, completed)
- Keep all share certificates (active, transferred, redeemed)
- Account-level data stays current

### 4. **Business Rules**
```php
// Check if member can apply for new loan
if (!$loanAccount->canAccommodateNewLoan($requestedAmount)) {
    return "Amount exceeds your loan limit";
}

// Check share balance
if (!$shareAccount->meetsMinimumRequirement()) {
    return "You must maintain minimum shares";
}
```

### 5. **Audit Trail**
- Account-level changes tracked separately
- Entity-level changes tracked in detail
- No mixing of concerns

---

## 🎓 **Key Takeaways**

1. **LoanAccount** ≠ **Loan**
   - LoanAccount = Member's loan facility (totals, limits)
   - Loan = Individual loan application

2. **ShareAccount** ≠ **Share**
   - ShareAccount = Member's share ownership (units, dividends)
   - Share = Individual share certificate/purchase

3. **One-to-Many Relationships**
   - One LoanAccount → Many Loans
   - One ShareAccount → Many Shares

4. **Polymorphic Accounts**
   - SavingsAccount: Direct transactional (no sub-entities needed)
   - LoanAccount: Aggregate tracker (has Loans)
   - ShareAccount: Aggregate tracker (has Shares)

---

## 🚀 **Status**

✅ **Migrations Created**
✅ **Models Updated**
✅ **Factories Updated**
✅ **Seeder Updated**
⚠️ **Need:** Foreign key migrations for loans/shares tables
⚠️ **Need:** Update Loan/Share models with account relationships

**Ready for testing after adding foreign key migrations!**
