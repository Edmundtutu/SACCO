<?php

namespace App\Services\Transactions;

use App\DTOs\LedgerEntryDTO;
use App\DTOs\TransactionDTO;
use App\Exceptions\InvalidTransactionException;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Transaction;
use App\Services\Transactions\TransactionHandlerInterface;

class LoanRepaymentHandler implements TransactionHandlerInterface
{
    protected LoanCalculationService $loanCalculationService;

    public function __construct(LoanCalculationService $loanCalculationService)
    {
        $this->loanCalculationService = $loanCalculationService;
    }

    public function validate(TransactionDTO $transactionData): void
    {
        // Verify loan exists and is active
        $loan = Loan::find($transactionData->relatedLoanId);
        if (!$loan) {
            throw new InvalidTransactionException("Loan not found");
        }

        if (!in_array($loan->status, ['disbursed', 'active'])) {
            throw new InvalidTransactionException("Loan is not active for repayment");
        }

        // Verify repayment amount is reasonable
        if ($transactionData->amount > $loan->outstanding_balance) {
            throw new InvalidTransactionException("Repayment amount exceeds outstanding balance");
        }

        $minRepayment = config('sacco.minimum_repayment_amount', 1000);
        if ($transactionData->amount < $minRepayment) {
            throw new InvalidTransactionException("Minimum repayment amount is {$minRepayment}");
        }
    }

    public function execute(Transaction $transaction, TransactionDTO $transactionData): void
    {
        $loan = Loan::find($transactionData->relatedLoanId);

        // Calculate payment allocation (principal vs interest)
        $paymentAllocation = $this->loanCalculationService->calculatePaymentAllocation(
            $loan,
            $transactionData->amount
        );

        // Create loan repayment record
        $repayment = LoanRepayment::create([
            'loan_id' => $loan->id,
            'receipt_number' => $transaction->transaction_number,
            'installment_number' => $this->getNextInstallmentNumber($loan->id),
            'scheduled_amount' => $transactionData->amount,
            'principal_amount' => $paymentAllocation['principal'],
            'interest_amount' => $paymentAllocation['interest'],
            'penalty_amount' => $paymentAllocation['penalty'] ?? 0,
            'total_amount' => $transactionData->amount,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => 'completed'
        ]);

        // Update loan balance
        $newOutstandingBalance = $loan->outstanding_balance - $paymentAllocation['principal'];
        $loan->update([
            'outstanding_balance' => $newOutstandingBalance,
            'total_paid' => $loan->total_paid + $transactionData->amount,
            'status' => $newOutstandingBalance <= 0 ? 'completed' : 'active'
        ]);

        // Store payment allocation in transaction metadata
        $transaction->update([
            'metadata' => json_encode([
                'principal_amount' => $paymentAllocation['principal'],
                'interest_amount' => $paymentAllocation['interest'],
                'penalty_amount' => $paymentAllocation['penalty'] ?? 0,
                'repayment_id' => $repayment->id
            ])
        ]);
    }

    public function getAccountingEntries(Transaction $transaction, TransactionDTO $transactionData): array
    {
        $metadata = json_decode($transaction->metadata, true);
        $principalAmount = $metadata['principal_amount'];
        $interestAmount = $metadata['interest_amount'];
        $penaltyAmount = $metadata['penalty_amount'] ?? 0;

        $entries = [
            new LedgerEntryDTO(
                accountCode: '1001',
                accountName: 'Cash in Hand',
                accountType: 'asset',
                debitAmount: $transactionData->amount,
                creditAmount: 0,
                description: "Loan repayment from member #{$transaction->member_id}"
            ),
            new LedgerEntryDTO(
                accountCode: '1100',
                accountName: 'Loans Receivable',
                accountType: 'asset',
                debitAmount: 0,
                creditAmount: $principalAmount,
                description: "Principal repayment for loan #{$transactionData->relatedLoanId}"
            ),
        ];

        // Add interest income entry if applicable
        if ($interestAmount > 0) {
            $entries[] = new LedgerEntryDTO(
                accountCode: '4001',
                accountName: 'Loan Interest Income',
                accountType: 'income',
                debitAmount: 0,
                creditAmount: $interestAmount,
                description: "Interest income from loan #{$transactionData->relatedLoanId}"
            );
        }

        // Add penalty income entry if applicable
        if ($penaltyAmount > 0) {
            $entries[] = new LedgerEntryDTO(
                accountCode: '4003',
                accountName: 'Penalty Income',
                accountType: 'income',
                debitAmount: 0,
                creditAmount: $penaltyAmount,
                description: "Penalty income from loan #{$transactionData->relatedLoanId}"
            );
        }

        return $entries;
    }

    protected function getNextInstallmentNumber(int $loanId): int
    {
        return LoanRepayment::where('loan_id', $loanId)->count() + 1;
    }
}
