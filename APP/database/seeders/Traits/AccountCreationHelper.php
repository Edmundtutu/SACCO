<?php

namespace Database\Seeders\Traits;

use App\Models\Account;
use App\Models\LoanAccount;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use Illuminate\Database\Eloquent\Model;

trait AccountCreationHelper
{
    /**
     * Create a polymorphic account with consistent patterns
     *
     * @param int $memberId The member ID
     * @param Model $accountable The accountable model (SavingsAccount, LoanAccount, ShareAccount)
     * @param string $status Account status (default: 'active')
     * @param string|null $accountNumber Optional custom account number (if null, will be auto-generated)
     * @param \DateTime|null $openingDate Opening date (default: now)
     * @return Account
     */
    protected function createAccount(
        int $memberId,
        Model $accountable,
        string $status = 'active',
        ?string $accountNumber = null,
        ?\DateTime $openingDate = null
    ): Account {
        // Let the model's boot method handle account number generation if not provided
        return Account::create([
            'account_number' => $accountNumber,
            'member_id' => $memberId,
            'accountable_type' => get_class($accountable),
            'accountable_id' => $accountable->id,
            'status' => $status,
        ]);
    }

    /**
     * Create a savings account with its parent Account
     *
     * @param int $memberId The member ID
     * @param int $savingsProductId The savings product ID
     * @param array $savingsAccountData Additional data for the SavingsAccount
     * @param string $status Account status (default: 'active')
     * @return Account
     */
    protected function createSavingsAccount(
        int $memberId,
        int $savingsProductId,
        array $savingsAccountData = [],
        string $status = 'active'
    ): Account {
        // Create the accountable SavingsAccount
        $savingsAccount = SavingsAccount::create(array_merge([
            'savings_product_id' => $savingsProductId,
            'balance' => 0,
            'available_balance' => 0,
            'minimum_balance' => 0,
            'interest_earned' => 0,
            'interest_rate' => 0,
            'last_interest_calculation' => now(),
            'maturity_date' => null,
        ], $savingsAccountData));

        // Create and return the parent Account
        return $this->createAccount($memberId, $savingsAccount, $status);
    }

    /**
     * Create a loan account with its parent Account
     *
     * @param int $memberId The member ID
     * @param array $loanAccountData Additional data for the LoanAccount
     * @param string $status Account status (default: 'active')
     * @return Account
     */
    protected function createLoanAccount(
        int $memberId,
        array $loanAccountData = [],
        string $status = 'active'
    ): Account {
        // Create the accountable LoanAccount
        $loanAccount = LoanAccount::create(array_merge([
            'total_disbursed_amount' => 0,
            'total_repaid_amount' => 0,
            'current_outstanding' => 0,
            'min_loan_limit' => 0,
            'max_loan_limit' => null,
            'repayment_frequency_type' => 'monthly',
            'status_notes' => null,
            'last_activity_date' => now(),
            'account_features' => [],
            'audit_trail' => [
                'created_by' => 'seeder',
                'created_at' => now()->toDateTimeString(),
            ],
            'remarks' => 'Created by AccountCreationHelper',
        ], $loanAccountData));

        // Create and return the parent Account
        return $this->createAccount($memberId, $loanAccount, $status);
    }

    /**
     * Create a share account with its parent Account
     *
     * @param int $memberId The member ID
     * @param array $shareAccountData Additional data for the ShareAccount
     * @param string $status Account status (default: 'active')
     * @return Account
     */
    protected function createShareAccount(
        int $memberId,
        array $shareAccountData = [],
        string $status = 'active'
    ): Account {
        // Create the accountable ShareAccount
        $shareAccount = ShareAccount::create(array_merge([
            'share_units' => 0,
            'share_price' => 1000, // Default share price
            'total_share_value' => 0,
            'dividends_earned' => 0,
            'dividends_pending' => 0,
            'dividends_paid' => 0,
            'last_activity_date' => now(),
        ], $shareAccountData));

        // Create and return the parent Account
        return $this->createAccount($memberId, $shareAccount, $status);
    }
}
