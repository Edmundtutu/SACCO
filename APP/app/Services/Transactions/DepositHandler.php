<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;
use App\Models\Account;
use App\Models\Transaction;

class DepositHandler implements TransactionHandlerInterface
{
    public function validate(TransactionDTO $transactionData): void
    {
        // Check minimum deposit amount
        $minDeposit = config('sacco.minimum_deposit_amount', 1000);
        if ($transactionData->amount < $minDeposit) {
            throw new InvalidTransactionException("Minimum deposit amount is {$minDeposit}");
        }

        // Verify account exists and is active
        $account = Account::find($transactionData->accountId);
        if (!$account) {
            throw new InvalidTransactionException("Account not found");
        }

        if ($account->status !== 'active') {
            throw new InvalidTransactionException("Account is not active");
        }

        // Check maximum daily deposit limit
        $dailyLimit = config('sacco.daily_deposit_limit', 1000000);
        $todayDeposits = Transaction::where('member_id', $transactionData->memberId)
            ->where('type', 'deposit')
            ->whereDate('transaction_date', now()->toDateString())
            ->where('status', 'completed')
            ->sum('amount');

        if (($todayDeposits + $transactionData->amount) > $dailyLimit) {
            throw new InvalidTransactionException("Daily deposit limit exceeded");
        }
    }

    public function execute(Transaction $transaction, TransactionDTO $transactionData): void
    {
        // For deposits, no additional business logic needed
        // The main service handles balance updates and ledger entries

        // Could add specific deposit logic here like:
        // - Updating member statistics
    }

    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        return [
            new LedgerEntryDTO(
                accountCode: '1001',
                accountName: 'Cash in Hand',
                accountType: 'asset',
                debitAmount: $transaction->amount,
                creditAmount: 0,
                description: "Cash deposit from member #{$transaction->member_id}"
            ),
            new LedgerEntryDTO(
                accountCode: '2001',
                accountName: 'Member Savings Payable',
                accountType: 'liability',
                debitAmount: 0,
                creditAmount: $transaction->amount,
                description: "Savings liability for member #{$transaction->member_id}"
            ),
        ];
    }
}
