<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;
use App\Models\ExpenseRecord;
use App\Models\Transaction;
use App\Services\PaymentMethodAccountResolver;
use Illuminate\Support\Str;

/**
 * Phase 2 — Expense transaction handler.
 *
 * GL posting (double-entry):
 *   Debit  : Expense account (category-specific, 5xxx)
 *   Credit : Payment asset account (1001/1002/1003 via PaymentMethodAccountResolver)
 *
 * A detail row is written to expense_records inside execute() so that
 * every expense is traceable from:
 *   expense_records → transactions → general_ledger (2 rows)
 */
class ExpenseHandler implements TransactionHandlerInterface
{
    public function validate(TransactionDTO $transactionData): void
    {
        $minAmount = config('financial.minimum_expense_amount', 1);
        if ($transactionData->amount < $minAmount) {
            throw new InvalidTransactionException(
                "Minimum expense amount is {$minAmount}"
            );
        }

        $category = $transactionData->metadata['category'] ?? null;
        if (empty($category)) {
            throw new InvalidTransactionException('Expense category is required');
        }

        $validCategories = array_keys(config('financial.expense_categories', []));
        if (!in_array($category, $validCategories, true)) {
            throw new InvalidTransactionException(
                "Invalid expense category: {$category}. Valid: " . implode(', ', $validCategories)
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
        $categories  = config('financial.expense_categories', []);
        $accountInfo = $categories[$category] ?? $categories['other'];

        ExpenseRecord::create([
            'transaction_id'    => $transaction->id,
            'category'          => $category,
            'gl_account_code'   => $accountInfo['code'],
            'gl_account_name'   => $accountInfo['name'],
            'amount'            => $transaction->amount,
            'payment_method'    => $transactionData->resolvePaymentMethod(),
            'payment_reference' => $transactionData->resolvePaymentReference(),
            'description'       => $transactionData->description,
            'receipt_number'    => $transaction->transaction_number,
            'recorded_by'       => $transactionData->processedBy,
            'tenant_id'         => $transaction->tenant_id ?? null,
        ]);
    }

    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        $category    = $transactionData->metadata['category'];
        $categories  = config('financial.expense_categories', []);
        $accountInfo = $categories[$category] ?? $categories['other'];

        $paymentAccount = PaymentMethodAccountResolver::resolve(
            $transactionData->resolvePaymentMethod()
        );

        return [
            // Debit the expense account (increases expense)
            new LedgerEntryDTO(
                accountCode: $accountInfo['code'],
                accountName: $accountInfo['name'],
                accountType: 'expense',
                debitAmount: $transaction->amount,
                creditAmount: 0,
                description: "Expense ({$category}): " . ($transactionData->description ?? $accountInfo['name'])
            ),
            // Credit the payment asset account (cash/bank/mobile money goes out)
            new LedgerEntryDTO(
                accountCode: $paymentAccount['account_code'],
                accountName: $paymentAccount['account_name'],
                accountType: $paymentAccount['account_type'],
                debitAmount: 0,
                creditAmount: $transaction->amount,
                description: "Payment for expense ({$category}) via {$transactionData->resolvePaymentMethod()}"
            ),
        ];
    }
}
