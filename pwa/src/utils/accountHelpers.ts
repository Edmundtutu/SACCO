import type { Account, SavingsAccount, LoanAccount, ShareAccount, Loan } from '@/types/api';

/**
 * Type guard to check if an account is a SavingsAccount
 */
export function isSavingsAccount(
  account: Account | null | undefined
): account is Account & { accountable: SavingsAccount } {
  return !!account && !!account.accountable_type && account.accountable_type.includes('SavingsAccount');
}

/**
 * Type guard to check if an account is a LoanAccount
 */
export function isLoanAccount(
  account: Account | null | undefined
): account is Account & { accountable: LoanAccount } {
  return !!account && !!account.accountable_type && account.accountable_type.includes('LoanAccount');
}

/**
 * Type guard to check if an account is a ShareAccount
 */
export function isShareAccount(
  account: Account | null | undefined
): account is Account & { accountable: ShareAccount } {
  return !!account && !!account.accountable_type && account.accountable_type.includes('ShareAccount');
}

/**
 * Get the SavingsAccount from an Account wrapper
 */
export function getSavingsAccount(account: Account | null | undefined): SavingsAccount | null {
  if (!account) return null;
  if (isSavingsAccount(account) && account.accountable) {
    return account.accountable;
  }
  return null;
}

/**
 * Get the LoanAccount from an Account wrapper
 */
export function getLoanAccount(account: Account | null | undefined): LoanAccount | null {
  if (!account) return null;
  if (isLoanAccount(account) && account.accountable) {
    return account.accountable;
  }
  return null;
}

/**
 * Get the ShareAccount from an Account wrapper
 */
export function getShareAccount(account: Account | null | undefined): ShareAccount | null {
  if (!account) return null;
  if (isShareAccount(account) && account.accountable) {
    return account.accountable;
  }
  return null;
}

/**
 * Calculate total savings balance from all accounts
 */
export function getTotalSavingsBalance(accounts: Account[] | null | undefined): number {
  if (!accounts || !Array.isArray(accounts)) return 0;

  return accounts
    .filter(isSavingsAccount)
    .reduce((sum, acc) => {
      const productType = acc.accountable?.savings_product?.type;
      const balance = Number(acc.accountable?.balance) || 0;

      // Include only if NOT a wallet-type savings account
      if (productType !== 'wallet') {
        return sum + balance;
      }

      // Skip wallet accounts (keep sum as is)
      return sum;
    }, 0);
}


/**
 * Calculate total available savings balance
 */
export function getTotalAvailableBalance(accounts: Account[] | null | undefined): number {
  if (!accounts || !Array.isArray(accounts)) return 0;
  return accounts
    .filter(isSavingsAccount)
    .reduce((sum, acc) => {
      const productType = acc.accountable?.savings_product?.type;
      const savings = acc.accountable;
      if(productType != 'wallet'){
        return sum + (Number(savings?.available_balance) || 0);
      }
      return sum;
    }, 0);
}

/**
 * Calculate total interest earned from savings accounts
 */
export function getTotalInterestEarned(accounts: Account[] | null | undefined): number {
  if (!accounts || !Array.isArray(accounts)) return 0;
  return accounts
    .filter(isSavingsAccount)
    .reduce((sum, acc) => {
      const productType = acc.accountable?.savings_product?.type;
      const savings = acc.accountable;
      if(productType != 'wallet'){
        return sum + (Number(savings?.interest_earned) || 0);
      }
      return sum;
    }, 0);
}

/**
 * Get total outstanding loan amount
 */
export function getTotalLoanOutstanding(accounts: Account[] | null | undefined): number {
  if (!accounts || !Array.isArray(accounts)) return 0;
  const loanAccount = accounts.find(isLoanAccount);
  if (loanAccount && loanAccount.accountable) {
    return loanAccount.accountable.current_outstanding || 0;
  }
  return 0;
}

/**
 * Get total disbursed loan amount
 */
export function getTotalLoanDisbursed(accounts: Account[]): number {
  const loanAccount = accounts.find(isLoanAccount);
  if (loanAccount && loanAccount.accountable) {
    return loanAccount.accountable.total_disbursed_amount || 0;
  }
  return 0;
}

/**
 * Get total repaid loan amount
 */
export function getTotalLoanRepaid(accounts: Account[]): number {
  const loanAccount = accounts.find(isLoanAccount);
  if (loanAccount && loanAccount.accountable) {
    return loanAccount.accountable.total_repaid_amount || 0;
  }
  return 0;
}

/**
 * Get total outstanding from individual Loan records
 * Use this when Loan data is fetched directly from /api/loans
 */
export function getTotalLoanOutstandingFromLoans(loans: Loan[] | null | undefined): number {
  if (!loans || !Array.isArray(loans)) return 0;
  
  return loans
    .filter(loan => ['active', 'disbursed', 'approved'].includes(loan.status))
    .reduce((total, loan) => total + (Number(loan.outstanding_balance) || 0), 0);
}

/**
 * Get total outstanding (smart function - uses best available source)
 * Prefers LoanAccount.current_outstanding if available, falls back to summing Loan records
 * 
 * @param accounts - Account array with potential LoanAccount
 * @param loans - Optional Loan array as fallback
 */
export function getSmartLoanOutstanding(
  accounts: Account[] | null | undefined,
  loans?: Loan[] | null | undefined
): number {
  // Prefer LoanAccount aggregate if available (SINGLE SOURCE OF TRUTH)
  const fromAccount = getTotalLoanOutstanding(accounts);
  if (fromAccount > 0) return fromAccount;
  
  // Fallback to summing individual loans if provided
  if (loans) {
    return getTotalLoanOutstandingFromLoans(loans);
  }
  
  return 0;
}

/**
 * Get total share value
 */
export function getTotalShareValue(accounts: Account[]): number {
  const shareAccount = accounts.find(isShareAccount);
  if (shareAccount && shareAccount.accountable) {
    return shareAccount.accountable.total_share_value || 0;
  }
  return 0;
}

/**
 * Get total share units
 */
export function getTotalShareUnits(accounts: Account[]): number {
  const shareAccount = accounts.find(isShareAccount);
  if (shareAccount && shareAccount.accountable) {
    return shareAccount.accountable.share_units || 0;
  }
  return 0;
}

/**
 * Get share price
 */
export function getSharePrice(accounts: Account[]): number {
  const shareAccount = accounts.find(isShareAccount);
  if (shareAccount && shareAccount.accountable) {
    return shareAccount.accountable.share_price || 1000;
  }
  return 1000; // Default
}

/**
 * Get total dividends earned
 */
export function getTotalDividendsEarned(accounts: Account[]): number {
  const shareAccount = accounts.find(isShareAccount);
  if (shareAccount && shareAccount.accountable) {
    return shareAccount.accountable.dividends_earned || 0;
  }
  return 0;
}

/**
 * Find a specific savings account by product code or id
 */
export function findSavingsAccount(
  accounts: Account[] | null | undefined,
  productCodeOrId: string | number
): Account | undefined {
  if (!accounts || !Array.isArray(accounts)) return undefined;
  return accounts.find((acc) => {
    if (!isSavingsAccount(acc)) return false;
    const savings = acc.accountable;
    if (typeof productCodeOrId === 'string') {
      return savings?.savings_product?.code === productCodeOrId;
    }
    return savings?.savings_product_id === productCodeOrId;
  });
}

/**
 * Find the wallet account (special savings product)
 */
export function findWalletAccount(accounts: Account[] | null | undefined): Account | undefined {
  return findSavingsAccount(accounts, 'WL001');
}

/**
 * Check if loan account can accommodate a new loan
 */
export function canAccommodateNewLoan(
  accounts: Account[] | null | undefined,
  requestedAmount: number
): { canAccommodate: boolean; reason?: string } {
  if (!accounts || !Array.isArray(accounts)) {
    return { canAccommodate: false, reason: 'No accounts provided' };
  }
  const loanAccountWrapper = accounts.find(isLoanAccount);
  
  if (!loanAccountWrapper || !loanAccountWrapper.accountable) {
    return { canAccommodate: false, reason: 'No loan account found' };
  }

  const loanAccount = loanAccountWrapper.accountable;
  const availableLimit = loanAccount.max_loan_limit - loanAccount.current_outstanding;

  if (requestedAmount > availableLimit) {
    return {
      canAccommodate: false,
      reason: `Loan amount exceeds available limit. Available: ${availableLimit}`,
    };
  }

  if (requestedAmount < loanAccount.min_loan_limit) {
    return {
      canAccommodate: false,
      reason: `Loan amount below minimum limit. Minimum: ${loanAccount.min_loan_limit}`,
    };
  }

  return { canAccommodate: true };
}

/**
 * Get account by account number
 */
export function findAccountByNumber(
  accounts: Account[] | null | undefined,
  accountNumber: string
): Account | undefined {
  if (!accounts || !Array.isArray(accounts)) return undefined;
  return accounts.find((acc) => acc.account_number === accountNumber);
}

/**
 * Filter active accounts
 */
export function getActiveAccounts(accounts: Account[] | null | undefined): Account[] {
  if (!accounts || !Array.isArray(accounts)) return [];
  return accounts.filter((acc) => acc.status === 'active');
}

/**
 * Get all savings accounts
 */
export function getAllSavingsAccounts(accounts: Account[] | null | undefined): Account[] {
  if (!accounts || !Array.isArray(accounts)) return [];
  return accounts.filter(isSavingsAccount);
}

/**
 * Get loan account wrapper
 */
export function getLoanAccountWrapper(accounts: Account[] | null | undefined): Account | undefined {
  if (!accounts || !Array.isArray(accounts)) return undefined;
  return accounts.find(isLoanAccount);
}

/**
 * Get share account wrapper
 */
export function getShareAccountWrapper(accounts: Account[] | null | undefined): Account | undefined {
  if (!accounts || !Array.isArray(accounts)) return undefined;
  return accounts.find(isShareAccount);
}
