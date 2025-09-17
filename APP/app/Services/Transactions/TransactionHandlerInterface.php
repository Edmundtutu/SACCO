<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Models\Transaction;

interface TransactionHandlerInterface
{
    /**
     * Validate transaction data
     */
    public function validate(TransactionDTO $transactionData): void;

    /**
     * Execute transaction-specific business logic
     */
    public function execute(Transaction $transaction, TransactionDTO $transactionData): void;

    /**
     * Get accounting entries for this transaction
     */
    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array;
}
