<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Exceptions\InsufficientBalanceException;
use App\Events\BalanceUpdated;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Update account balance after transaction
     */
    public function updateAccountBalance(Transaction $transaction): void
    {
        if (!$transaction->account_id) {
            return; // No account to update (e.g., loan disbursements)
        }

        // Lock the account row to prevent concurrent updates
        $account = Account::with('accountable')->lockForUpdate()->find($transaction->account_id);

        // Only update balance for savings accounts
        if (!$account->isSavingsAccount()) {
            return; // Loans and shares don't have transactional balances
        }

        $savingsAccount = $account->accountable;
        $previousBalance = $savingsAccount->balance;

        // Calculate new balance based on transaction type
        switch ($transaction->type) {
            case 'deposit':
            case 'wallet_topup':
                $savingsAccount->balance += $transaction->net_amount;
                $savingsAccount->available_balance += $transaction->net_amount;
                break;
            case 'withdrawal':
            case 'wallet_withdrawal':
            case 'wallet_to_savings':
            case 'wallet_to_loan':
                $savingsAccount->balance -= $transaction->amount; // Full amount including fees
                $savingsAccount->available_balance -= $transaction->amount;
                break;
            default:
                // For other transaction types, no direct balance impact
                break;
        }

        $savingsAccount->last_transaction_date = now();
        $savingsAccount->save();

        // Update transaction with balance information
        $transaction->update([
            'balance_before' => $previousBalance,
            'balance_after' => $savingsAccount->balance
        ]);

        // Fire balance updated event
        event(new BalanceUpdated($account->id, $previousBalance, $savingsAccount->balance));

        // Update interest earned if applicable
        $this->updateInterestEarned($account);
    }

    /**
     * Reverse account balance for cancelled transactions
     */
    public function reverseAccountBalance(Transaction $originalTransaction): void
    {
        if (!$originalTransaction->account_id) {
            return;
        }

        $account = Account::with('accountable')->lockForUpdate()->find($originalTransaction->account_id);

        // Only reverse balance for savings accounts
        if (!$account->isSavingsAccount()) {
            return;
        }

        $savingsAccount = $account->accountable;

        // Reverse the original transaction's effect
        switch ($originalTransaction->type) {
            case 'deposit':
            case 'wallet_topup':
                $savingsAccount->balance -= $originalTransaction->net_amount;
                $savingsAccount->available_balance -= $originalTransaction->net_amount;
                break;
            case 'withdrawal':
            case 'wallet_withdrawal':
            case 'wallet_to_savings':
            case 'wallet_to_loan':
                $savingsAccount->balance += $originalTransaction->amount;
                $savingsAccount->available_balance += $originalTransaction->amount;
                break;
        }

        $savingsAccount->save();
    }

    /**
     * Get available balance for withdrawal
     */
    public function getAvailableBalance(Account $account): float
    {
        // Only savings accounts have withdrawable balances
        if (!$account->isSavingsAccount()) {
            return 0;
        }

        $savingsAccount = $account->accountable;
        $currentBalance = $savingsAccount->balance;

        // Subtract minimum balance requirement
        $minBalance = $savingsAccount->savingsProduct ? $savingsAccount->savingsProduct->minimum_balance : $savingsAccount->minimum_balance;

        // Subtract any pending withdrawals
        $pendingWithdrawals = Transaction::where('account_id', $account->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->sum('amount');

        // Subtract loan obligations if configured
        $loanObligations = $this->getLoanObligations($account->member_id);

        return max(0, $currentBalance - $minBalance - $pendingWithdrawals - $loanObligations);
    }

    /**
     * Update interest earned on savings account
     */
    protected function updateInterestEarned(Account $account): void
    {
        // Only calculate interest for savings accounts
        if (!$account->isSavingsAccount()) {
            return;
        }

        $savingsAccount = $account->accountable;
        
        if (!$savingsAccount->savingsProduct || !$savingsAccount->savingsProduct->interest_rate) {
            return;
        }

        $interestCalculationMethod = $savingsAccount->savingsProduct->interest_calculation ?? 'daily_balance';

        if ($interestCalculationMethod === 'daily_balance') {
            $dailyInterest = ($savingsAccount->balance * $savingsAccount->savingsProduct->interest_rate / 100) / 365;
            $savingsAccount->increment('interest_earned', $dailyInterest);
        }
    }

    /**
     * Get loan obligations that affect available balance
     */
    protected function getLoanObligations(int $memberId): float
    {
        // This could include overdue loan payments, guaranteed loan amounts, etc.
        // Implementation depends on business rules
        return 0;
    }

    /**
     * Validate sufficient balance for withdrawal
     */
    public function validateSufficientBalance(Account $account, float $amount): void
    {
        $availableBalance = $this->getAvailableBalance($account);

        if ($availableBalance < $amount) {
            throw new InsufficientBalanceException(
                "Insufficient balance. Available: {$availableBalance}, Requested: {$amount}"
            );
        }
    }
}
