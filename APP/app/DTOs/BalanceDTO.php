<?php

namespace App\DTOs;

class BalanceDTO
{
    public function __construct(
        public int $accountId,
        public float $currentBalance,
        public float $availableBalance,
        public float $pendingTransactions,
        public float $minimumBalance,
        public float $interestEarned,
        public ?\DateTime $lastTransactionDate = null
    ) {}

    /**
     * Create from Account model
     */
    public static function fromAccount($account, float $availableBalance = null): self
    {
        return new self(
            accountId: $account->id,
            currentBalance: $account->balance,
            availableBalance: $availableBalance ?? $account->available_balance,
            pendingTransactions: 0, // Calculate this separately
            minimumBalance: $account->minimum_balance ?? 0,
            interestEarned: $account->interest_earned ?? 0,
            lastTransactionDate: $account->last_transaction_date
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'current_balance' => $this->currentBalance,
            'available_balance' => $this->availableBalance,
            'pending_transactions' => $this->pendingTransactions,
            'minimum_balance' => $this->minimumBalance,
            'interest_earned' => $this->interestEarned,
            'last_transaction_date' => $this->lastTransactionDate?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check if account has sufficient balance for withdrawal
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->availableBalance >= $amount;
    }

    /**
     * Get the restricted amount (current - available)
     */
    public function getRestrictedAmount(): float
    {
        return max(0, $this->currentBalance - $this->availableBalance);
    }
}
