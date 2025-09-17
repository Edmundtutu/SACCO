<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;
use App\Models\Loan;
use App\Models\Transaction;

class LoanDisbursementHandler implements TransactionHandlerInterface
{
    public function validate(TransactionDTO $transactionData): void
    {
        // Verify loan exists and is approved
        $loan = Loan::find($transactionData->relatedLoanId);
        if (!$loan) {
            throw new InvalidTransactionException("Loan not found");
        }

        if ($loan->status !== 'approved') {
            throw new InvalidTransactionException("Loan is not approved for disbursement");
        }

        // Verify disbursement amount matches loan amount
        if ($transactionData->amount != $loan->principal_amount) {
            throw new InvalidTransactionException("Disbursement amount must match loan principal amount");
        }

        // Check if loan has already been disbursed
        if ($loan->status === 'disbursed' || $loan->status === 'active') {
            throw new InvalidTransactionException("Loan has already been disbursed");
        }
    }

    public function execute(Transaction $transaction, TransactionDTO $transactionData): void
    {
        // Update loan status to disbursed
        $loan = Loan::find($transactionData->relatedLoanId);
        $loan->update([
            'status' => 'disbursed',
            'disbursement_date' => now(),
            'outstanding_balance' => $loan->principal_amount
        ]);
    }

    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        return [
            new LedgerEntryDTO(
                accountCode: '1100',
                accountName: 'Loans Receivable',
                accountType: 'asset',
                debitAmount: $transaction->amount,
                creditAmount: 0,
                description: "Loan disbursed to member #{$transaction->member_id}"
            ),
            new LedgerEntryDTO(
                accountCode: '1001',
                accountName: 'Cash in Hand',
                accountType: 'asset',
                debitAmount: 0,
                creditAmount: $transaction->amount,
                description: "Cash disbursed for loan #{$transactionData->relatedLoanId}"
            ),
        ];
    }
}
