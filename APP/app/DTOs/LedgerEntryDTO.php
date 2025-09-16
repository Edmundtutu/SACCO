<?php

namespace App\DTOs;

class LedgerEntryDTO
{
    public function __construct(
        public string $accountCode,
        public string $accountName,
        public string $accountType,
        public float $debitAmount,
        public float $creditAmount,
        public string $description,
        public ?string $referenceType = null,
        public ?int $referenceId = null
    ) {}

    /**
     * Create a debit entry
     */
    public static function debit(
        string $accountCode,
        string $accountName,
        string $accountType,
        float $amount,
        string $description
    ): self {
        return new self(
            accountCode: $accountCode,
            accountName: $accountName,
            accountType: $accountType,
            debitAmount: $amount,
            creditAmount: 0,
            description: $description
        );
    }

    /**
     * Create a credit entry
     */
    public static function credit(
        string $accountCode,
        string $accountName,
        string $accountType,
        float $amount,
        string $description
    ): self {
        return new self(
            accountCode: $accountCode,
            accountName: $accountName,
            accountType: $accountType,
            debitAmount: 0,
            creditAmount: $amount,
            description: $description
        );
    }

    /**
     * Convert to array for database insertion
     */
    public function toArray(): array
    {
        return [
            'account_code' => $this->accountCode,
            'account_name' => $this->accountName,
            'account_type' => $this->accountType,
            'debit_amount' => $this->debitAmount,
            'credit_amount' => $this->creditAmount,
            'description' => $this->description,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
        ];
    }

    /**
     * Validate the entry
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->accountCode)) {
            $errors[] = 'Account code is required';
        }

        if (empty($this->accountName)) {
            $errors[] = 'Account name is required';
        }

        if (!in_array($this->accountType, ['asset', 'liability', 'equity', 'income', 'expense'])) {
            $errors[] = 'Invalid account type';
        }

        if ($this->debitAmount < 0 || $this->creditAmount < 0) {
            $errors[] = 'Debit and credit amounts cannot be negative';
        }

        if ($this->debitAmount > 0 && $this->creditAmount > 0) {
            $errors[] = 'Entry cannot have both debit and credit amounts';
        }

        if ($this->debitAmount == 0 && $this->creditAmount == 0) {
            $errors[] = 'Entry must have either debit or credit amount';
        }

        if (empty($this->description)) {
            $errors[] = 'Description is required';
        }

        return $errors;
    }

    /**
     * Check if entry is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    /**
     * Get the net amount (debit - credit)
     */
    public function getNetAmount(): float
    {
        return $this->debitAmount - $this->creditAmount;
    }

    /**
     * Check if this is a debit entry
     */
    public function isDebit(): bool
    {
        return $this->debitAmount > 0;
    }

    /**
     * Check if this is a credit entry
     */
    public function isCredit(): bool
    {
        return $this->creditAmount > 0;
    }
}
