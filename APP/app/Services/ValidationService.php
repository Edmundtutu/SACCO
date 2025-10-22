<?php

namespace App\Services;

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;

class ValidationService
{
    /**
     * Validate business rules for a transaction
     */
    public function validateBusinessRules(TransactionDTO $transactionData): void
    {
        $this->validateAmount($transactionData->amount);
        $this->validateTransactionType($transactionData->type);
        $this->validateMemberStatus($transactionData->memberId);

        if ($transactionData->accountId) {
            $this->validateAccountStatus($transactionData->accountId);
        }
    }

    /**
     * Validate transaction amount
     */
    protected function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidTransactionException("Transaction amount must be greater than zero");
        }

        $maxTransactionAmount = config('sacco.max_transaction_amount', 10000000);
        if ($amount > $maxTransactionAmount) {
            throw new InvalidTransactionException("Transaction amount exceeds maximum allowed limit");
        }
    }

    /**
     * Validate transaction type
     */
    protected function validateTransactionType(string $type): void
    {
        $allowedTypes = [
            'deposit',
            'withdrawal',
            'share_purchase',
            'loan_disbursement',
            'loan_repayment',
            'reversal',
            'wallet_topup',
            'wallet_withdrawal',
            'wallet_to_savings',
            'wallet_to_loan',
        ];

        if (!in_array($type, $allowedTypes)) {
            throw new InvalidTransactionException("Invalid transaction type: {$type}");
        }
    }

    /**
     * Validate member status
     */
    public function validateMemberStatus(int $memberId): void
    {
        $member = User::find($memberId);

        if (!$member) {
            throw new InvalidTransactionException("Member not found");
        }

        if ($member->status !== 'active') {
            throw new InvalidTransactionException("Member account is not active");
        }

        // Check if member has any restrictions
        $membership = $member->membership;
        if ($membership && $membership->approval_status !== 'approved') {
            throw new InvalidTransactionException("Member is not fully approved for transactions");
        }
    }

    /**
     * Validate account status
     */
    public function validateAccountStatus(int $accountId): void
    {
        $account = Account::find($accountId);

        if (!$account) {
            throw new InvalidTransactionException("Account not found");
        }

        if ($account->status !== 'active') {
            throw new InvalidTransactionException("Account is not active");
        }
    }

    /**
     * Validate daily transaction limits
     */
    public function validateDailyLimits(TransactionDTO $transactionData): void
    {
        $today = now()->toDateString();

        // Get today's transactions for this member
        $todayTransactions = Transaction::where('member_id', $transactionData->memberId)
            ->whereDate('transaction_date', $today)
            ->where('status', 'completed')
            ->get();

        // Check daily transaction count limit
        $maxDailyTransactions = config('sacco.max_daily_transactions', 10);
        if ($todayTransactions->count() >= $maxDailyTransactions) {
            throw new InvalidTransactionException("Daily transaction limit exceeded");
        }

        // Check daily amount limits by type
        $this->validateDailyAmountLimits($transactionData, $todayTransactions);
    }

    /**
     * Validate daily amount limits by transaction type
     */
    protected function validateDailyAmountLimits(TransactionDTO $transactionData, $todayTransactions): void
    {
        $limits = [
            'deposit' => config('sacco.daily_deposit_limit', 1000000),
            'withdrawal' => config('sacco.daily_withdrawal_limit', 500000),
            'share_purchase' => config('sacco.daily_share_purchase_limit', 1000000),
            'wallet_topup' => config('sacco.wallet_daily_limit', 5000000),
            'wallet_withdrawal' => config('sacco.wallet_daily_limit', 5000000),
            'wallet_to_savings' => config('sacco.wallet_daily_limit', 5000000),
            'wallet_to_loan' => config('sacco.wallet_daily_limit', 5000000),
        ];

        if (!isset($limits[$transactionData->type])) {
            return; // No limit for this transaction type
        }

        $todayAmount = $todayTransactions
            ->where('type', $transactionData->type)
            ->sum('amount');

        $limit = $limits[$transactionData->type];

        if (($todayAmount + $transactionData->amount) > $limit) {
            throw new InvalidTransactionException("Daily {$transactionData->type} limit of {$limit} exceeded");
        }
    }
}
