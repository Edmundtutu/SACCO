<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Exceptions\InsufficientBalanceException;
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
        $account = Account::lockForUpdate()->find($transaction->account_id);

        $previousBalance = $account->balance;

        // Calculate new balance based on transaction type
        switch ($transaction->type) {
            case 'deposit':
                $account->balance += $transaction->net_amount;
                break;
            case 'withdrawal':
                $account->balance -= $transaction->amount; // Full amount including fees
                break;
            default:
                // For other transaction types, no direct balance impact
                break;
        }

        $account->save();

        // Update transaction with balance information
        $transaction->update([
            'balance_before' => $previousBalance,
            'balance_after' => $account->balance
        ]);

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

        $account = Account::lockForUpdate()->find($originalTransaction->account_id);

        // Reverse the original transaction's effect
        switch ($originalTransaction->type) {
            case 'deposit':
                $account->balance -= $originalTransaction->net_amount;
                break;
            case 'withdrawal':
                $account->balance += $originalTransaction->amount;
                break;
        }

        $account->save();
    }

    /**
     * Get available balance for withdrawal
     */
    public function getAvailableBalance(Account $account): float
    {
        $currentBalance = $account->balance;

        // Subtract minimum balance requirement
        $minBalance = $account->savingsProduct->minimum_balance ?? 0;

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
        if (!$account->savingsProduct->interest_rate) {
            return;
        }

        $interestCalculationMethod = $account->savingsProduct->interest_calculation ?? 'daily_balance';

        if ($interestCalculationMethod === 'daily_balance') {
            $dailyInterest = ($account->balance * $account->savingsProduct->interest_rate / 100) / 365;
            $account->increment('interest_earned', $dailyInterest);
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
