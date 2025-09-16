<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;
use App\Models\Account;
use App\Models\Transaction;

class WithdrawalHandler implements TransactionHandlerInterface
{
    public function validate(TransactionDTO $transactionData): void
    {
        // Verify account exists and is active
        $account = Account::find($transactionData->accountId);
        if (!$account) {
            throw new InvalidTransactionException("Account not found");
        }

        if ($account->status !== 'active') {
            throw new InvalidTransactionException("Account is not active");
        }

        // Check available balance
        $availableBalance = $account->balance; // Could be enhanced with app(BalanceService::class)->getAvailableBalance($account)

        if ($availableBalance < $transactionData->amount) {
            throw new InsufficientBalanceException(
                "Insufficient balance. Available: {$availableBalance}, Requested: {$transactionData->amount}"
            );
        }

        // Check minimum balance requirement
        $minBalance = $account->savingsProduct->minimum_balance ?? 0;
        if (($availableBalance - $transactionData->amount) < $minBalance) {
            throw new InvalidTransactionException("Withdrawal would breach minimum balance requirement");
        }

        // Check daily withdrawal limits
        $dailyLimit = config('sacco.daily_withdrawal_limit', 500000);
        $todayWithdrawals = Transaction::where('member_id', $transactionData->memberId)
            ->where('type', 'withdrawal')
            ->whereDate('transaction_date', now()->toDateString())
            ->where('status', 'completed')
            ->sum('amount');

        if (($todayWithdrawals + $transactionData->amount) > $dailyLimit) {
            throw new InvalidTransactionException("Daily withdrawal limit exceeded");
        }
    }

    public function execute(Transaction $transaction, TransactionDTO $transactionData): void
    {
        // Apply withdrawal fee if configured
        $account = Account::find($transactionData->accountId);
        $withdrawalFee = $account->savingsProduct->withdrawal_fee ?? 0;

        if ($withdrawalFee > 0 && $transaction->fee_amount == 0) {
            $transaction->update([
                'fee_amount' => $withdrawalFee,
                'net_amount' => $transaction->amount - $withdrawalFee
            ]);
        }
    }

    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        $entries = [
            new LedgerEntryDTO(
                accountCode: '2001',
                accountName: 'Member Savings Payable',
                accountType: 'liability',
                debitAmount: $transaction->amount,
                creditAmount: 0,
                description: "Cash withdrawal by member #{$transaction->member_id}"
            ),
            new LedgerEntryDTO(
                accountCode: '1001',
                accountName: 'Cash in Hand',
                accountType: 'asset',
                debitAmount: 0,
                creditAmount: $transaction->net_amount,
                description: "Cash paid to member #{$transaction->member_id}"
            ),
        ];

        // Add fee entry if applicable
        if ($transaction->fee_amount > 0) {
            $entries[] = new LedgerEntryDTO(
                accountCode: '4002',
                accountName: 'Fee Income',
                accountType: 'income',
                debitAmount: 0,
                creditAmount: $transaction->fee_amount,
                description: "Withdrawal fee from member #{$transaction->member_id}"
            );
        }

        return $entries;
    }
}
