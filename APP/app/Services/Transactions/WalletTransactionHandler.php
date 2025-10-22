<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Loan;

class WalletTransactionHandler implements TransactionHandlerInterface
{
    /**
     * Validate wallet transaction
     */
    public function validate(TransactionDTO $transactionData): void
    {
        // Verify account exists and is a wallet account
        $account = Account::find($transactionData->accountId);
        
        if (!$account) {
            throw new InvalidTransactionException("Account not found");
        }

        if ($account->status !== 'active') {
            throw new InvalidTransactionException("Account is not active");
        }

        // Verify it's a wallet account
        if ($account->savingsProduct && $account->savingsProduct->type !== 'wallet') {
            throw new InvalidTransactionException("This is not a wallet account");
        }

        // For withdrawals and transfers, check sufficient balance
        if (in_array($transactionData->type, ['wallet_withdrawal', 'wallet_to_savings', 'wallet_to_loan'])) {
            if ($account->balance < $transactionData->amount) {
                throw new InsufficientBalanceException(
                    "Insufficient wallet balance. Available: {$account->balance}, Requested: {$transactionData->amount}"
                );
            }
        }

        // Validate minimum transaction amounts
        $minAmount = config('sacco.wallet_minimum_transaction', 500);
        if ($transactionData->amount < $minAmount) {
            throw new InvalidTransactionException("Minimum wallet transaction amount is {$minAmount}");
        }

        // Check daily wallet limits
        $this->validateDailyLimits($transactionData);

        // Additional validation for wallet-to-loan transactions
        if ($transactionData->type === 'wallet_to_loan' && !$transactionData->relatedLoanId) {
            throw new InvalidTransactionException("Loan ID is required for wallet-to-loan transactions");
        }
    }

    /**
     * Execute wallet transaction business logic
     */
    public function execute(Transaction $transaction, TransactionDTO $transactionData): void
    {
        // For wallet transactions, typically no fees
        // But we can add logic here if needed
        
        // For wallet-to-loan transactions, update loan balances
        if ($transactionData->type === 'wallet_to_loan') {
            $this->processLoanRepayment($transaction, $transactionData);
        }
    }

    /**
     * Get accounting entries for wallet transactions
     */
    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        return match($transactionData->type) {
            'wallet_topup' => $this->getWalletTopupEntries($transaction),
            'wallet_withdrawal' => $this->getWalletWithdrawalEntries($transaction),
            'wallet_to_savings' => $this->getWalletToSavingsEntries($transaction),
            'wallet_to_loan' => $this->getWalletToLoanEntries($transaction, $transactionData),
            default => throw new InvalidTransactionException("Unknown wallet transaction type: {$transactionData->type}"),
        };
    }

    /**
     * Wallet Top-up: Member deposits cash to wallet
     * Debit: 1004 Wallet Control (Cash)
     * Credit: 2004 Member Wallet Liability
     */
    protected function getWalletTopupEntries(Transaction $transaction): array
    {
        return [
            new LedgerEntryDTO(
                accountCode: '1004',
                accountName: 'Wallet Control (Cash)',
                accountType: 'asset',
                debitAmount: (float) $transaction->amount,
                creditAmount: 0.0,
                description: "Wallet top-up by member #{$transaction->member_id} - {$transaction->transaction_number}"
            ),
            new LedgerEntryDTO(
                accountCode: '2004',
                accountName: 'Member Wallet Liability',
                accountType: 'liability',
                debitAmount: 0.0,
                creditAmount: (float) $transaction->amount,
                description: "Wallet liability for member #{$transaction->member_id} - {$transaction->transaction_number}"
            ),
        ];
    }

    /**
     * Wallet Withdrawal: Member withdraws cash from wallet
     * Debit: 2004 Member Wallet Liability
     * Credit: 1004 Wallet Control (Cash)
     */
    protected function getWalletWithdrawalEntries(Transaction $transaction): array
    {
        return [
            new LedgerEntryDTO(
                accountCode: '2004',
                accountName: 'Member Wallet Liability',
                accountType: 'liability',
                debitAmount: (float) $transaction->amount,
                creditAmount: 0.0,
                description: "Wallet withdrawal by member #{$transaction->member_id} - {$transaction->transaction_number}"
            ),
            new LedgerEntryDTO(
                accountCode: '1004',
                accountName: 'Wallet Control (Cash)',
                accountType: 'asset',
                debitAmount: 0.0,
                creditAmount: (float) $transaction->amount,
                description: "Cash paid to member #{$transaction->member_id} - {$transaction->transaction_number}"
            ),
        ];
    }

    /**
     * Wallet to Savings: Member uses wallet to deposit into savings
     * Debit: 2004 Member Wallet Liability
     * Credit: 2001 Member Savings
     */
    protected function getWalletToSavingsEntries(Transaction $transaction): array
    {
        return [
            new LedgerEntryDTO(
                accountCode: '2004',
                accountName: 'Member Wallet Liability',
                accountType: 'liability',
                debitAmount: (float) $transaction->amount,
                creditAmount: 0.0,
                description: "Wallet to savings transfer by member #{$transaction->member_id} - {$transaction->transaction_number}"
            ),
            new LedgerEntryDTO(
                accountCode: '2001',
                accountName: 'Member Savings Payable',
                accountType: 'liability',
                debitAmount: 0.0,
                creditAmount: (float) $transaction->amount,
                description: "Savings deposit from wallet for member #{$transaction->member_id} - {$transaction->transaction_number}"
            ),
        ];
    }

    /**
     * Wallet to Loan: Member uses wallet to repay loan
     * Debit: 2004 Member Wallet Liability
     * Credit: 1100 Loans Receivable (principal) / 1200 Interest Receivable
     */
    protected function getWalletToLoanEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        $loan = Loan::find($transactionData->relatedLoanId);
        
        if (!$loan) {
            throw new InvalidTransactionException("Loan not found");
        }

        // Calculate principal and interest portions
        $transactionAmount = (float) $transaction->amount;
        $interestBalance = (float) ($loan->interest_balance ?? 0);
        $interestDue = min($transactionAmount, $interestBalance);
        $principalPayment = $transactionAmount - $interestDue;

        $entries = [
            new LedgerEntryDTO(
                accountCode: '2004',
                accountName: 'Member Wallet Liability',
                accountType: 'liability',
                debitAmount: $transactionAmount,
                creditAmount: 0.0,
                description: "Wallet to loan repayment by member #{$transaction->member_id} for loan #{$loan->loan_number}"
            ),
        ];

        // Add interest portion if any
        if ($interestDue > 0) {
            $entries[] = new LedgerEntryDTO(
                accountCode: '1200',
                accountName: 'Interest Receivable',
                accountType: 'income',
                debitAmount: 0.0,
                creditAmount: $interestDue,
                description: "Interest payment from wallet for loan #{$loan->loan_number}"
            );
        }

        // Add principal portion
        if ($principalPayment > 0) {
            $entries[] = new LedgerEntryDTO(
                accountCode: '1100',
                accountName: 'Loans Receivable',
                accountType: 'asset',
                debitAmount: 0.0,
                creditAmount: $principalPayment,
                description: "Principal payment from wallet for loan #{$loan->loan_number}"
            );
        }

        return $entries;
    }

    /**
     * Process loan repayment from wallet
     */
    protected function processLoanRepayment(Transaction $transaction, TransactionDTO $transactionData): void
    {
        $loan = Loan::find($transactionData->relatedLoanId);
        
        if (!$loan) {
            return;
        }

        // Update loan balances
        $remainingAmount = (float) $transaction->amount;

        // First pay interest
        if ($loan->interest_balance > 0) {
            $interestPayment = min($remainingAmount, (float) $loan->interest_balance);
            $loan->interest_balance = (float) $loan->interest_balance - $interestPayment;
            $remainingAmount -= $interestPayment;
        }

        // Then pay principal
        if ($remainingAmount > 0 && $loan->principal_balance > 0) {
            $principalPayment = min($remainingAmount, (float) $loan->principal_balance);
            $loan->principal_balance = (float) $loan->principal_balance - $principalPayment;
            $loan->outstanding_balance = (float) $loan->outstanding_balance - $principalPayment;
            $loan->total_paid = (float) $loan->total_paid + $principalPayment;
        }

        // Update loan status if fully paid
        if ($loan->outstanding_balance <= 0) {
            $loan->status = 'completed';
        }

        $loan->save();
    }

    /**
     * Validate daily wallet transaction limits
     */
    protected function validateDailyLimits(TransactionDTO $transactionData): void
    {
        $today = now()->toDateString();
        
        $todayTransactions = Transaction::where('member_id', $transactionData->memberId)
            ->whereIn('type', ['wallet_topup', 'wallet_withdrawal', 'wallet_to_savings', 'wallet_to_loan'])
            ->whereDate('transaction_date', $today)
            ->where('status', 'completed')
            ->sum('amount');

        $dailyLimit = config('sacco.wallet_daily_limit', 5000000);
        
        if (($todayTransactions + $transactionData->amount) > $dailyLimit) {
            throw new InvalidTransactionException("Daily wallet transaction limit of {$dailyLimit} exceeded");
        }
    }
}
