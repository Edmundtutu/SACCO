<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;
use App\Models\IncomeRecord;
use App\Models\Transaction;
use App\Services\PaymentMethodAccountResolver;

/**
 * Phase 2 — Income (non-loan) transaction handler.
 *
 * GL posting (double-entry):
 *   Debit  : Payment asset account (1001/1002/1003 via PaymentMethodAccountResolver)
 *   Credit : Income account (category-specific, 4xxx)
 *
 * A detail row is written to income_records inside execute() so that
 * every income entry is traceable from:
 *   income_records → transactions → general_ledger (2 rows)
 */
class IncomeHandler implements TransactionHandlerInterface
{
    public function validate(TransactionDTO $transactionData): void
    {
        $minAmount = config('financial.minimum_income_amount', 1);
        if ($transactionData->amount < $minAmount) {
            throw new InvalidTransactionException(
                "Minimum income amount is {$minAmount}"
            );
        }

        $category = $transactionData->metadata['category'] ?? null;
        if (empty($category)) {
            throw new InvalidTransactionException('Income category is required');
        }

        $validCategories = array_keys(config('financial.income_categories', []));
        if (!in_array($category, $validCategories, true)) {
            throw new InvalidTransactionException(
                "Invalid income category: {$category}. Valid: " . implode(', ', $validCategories)
            );
        }

        $allowedMethods = ['cash', 'bank_transfer', 'mobile_money'];
        $method = $transactionData->resolvePaymentMethod();
        if (!in_array($method, $allowedMethods, true)) {
            throw new InvalidTransactionException(
                "Invalid payment method: {$method}. Valid: " . implode(', ', $allowedMethods)
            );
        }
    }

    public function execute(Transaction $transaction, TransactionDTO $transactionData): void
    {
        $category    = $transactionData->metadata['category'];
        $categories  = config('financial.income_categories', []);
        $accountInfo = $categories[$category] ?? $categories['other'];

        IncomeRecord::create([
            'transaction_id'    => $transaction->id,
            'category'          => $category,
            'gl_account_code'   => $accountInfo['code'],
            'gl_account_name'   => $accountInfo['name'],
            'amount'            => $transaction->amount,
            'payment_method'    => $transactionData->resolvePaymentMethod(),
            'payment_reference' => $transactionData->resolvePaymentReference(),
            'description'       => $transactionData->description,
            'receipt_number'    => $transaction->transaction_number,
            'payer_member_id'   => $transactionData->metadata['payer_member_id'] ?? null,
            'recorded_by'       => $transactionData->processedBy,
            'tenant_id'         => $transaction->tenant_id ?? null,
        ]);
    }

    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        $category    = $transactionData->metadata['category'];
        $categories  = config('financial.income_categories', []);
        $accountInfo = $categories[$category] ?? $categories['other'];

        $paymentAccount = PaymentMethodAccountResolver::resolve(
            $transactionData->resolvePaymentMethod()
        );

        return [
            // Debit the payment asset account (cash/bank/mobile money comes in)
            new LedgerEntryDTO(
                accountCode: $paymentAccount['account_code'],
                accountName: $paymentAccount['account_name'],
                accountType: $paymentAccount['account_type'],
                debitAmount: $transaction->amount,
                creditAmount: 0,
                description: "Receipt for income ({$category}) via {$transactionData->resolvePaymentMethod()}"
            ),
            // Credit the income account (increases income)
            new LedgerEntryDTO(
                accountCode: $accountInfo['code'],
                accountName: $accountInfo['name'],
                accountType: 'income',
                debitAmount: 0,
                creditAmount: $transaction->amount,
                description: "Income ({$category}): " . ($transactionData->description ?? $accountInfo['name'])
            ),
        ];
    }
}
