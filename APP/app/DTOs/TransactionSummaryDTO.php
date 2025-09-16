<?php

namespace App\DTOs;

class TransactionSummaryDTO
{
    public function __construct(
        public int $memberId,
        public int $totalTransactions,
        public float $totalDeposits,
        public float $totalWithdrawals,
        public float $totalLoanDisbursements,
        public float $totalLoanRepayments,
        public float $totalSharePurchases,
        public float $totalFees,
        public float $netCashFlow,
        public ?\DateTime $periodStart = null,
        public ?\DateTime $periodEnd = null
    ) {}

    /**
     * Create from transaction collection
     */
    public static function fromTransactions($transactions, int $memberId, $periodStart = null, $periodEnd = null): self
    {
        $summary = [
            'total_transactions' => $transactions->count(),
            'total_deposits' => $transactions->where('type', 'deposit')->sum('amount'),
            'total_withdrawals' => $transactions->where('type', 'withdrawal')->sum('amount'),
            'total_loan_disbursements' => $transactions->where('type', 'loan_disbursement')->sum('amount'),
            'total_loan_repayments' => $transactions->where('type', 'loan_repayment')->sum('amount'),
            'total_share_purchases' => $transactions->where('type', 'share_purchase')->sum('amount'),
            'total_fees' => $transactions->sum('fee_amount'),
        ];

        // Calculate net cash flow
        $inflows = $summary['total_deposits'] + $summary['total_loan_repayments'];
        $outflows = $summary['total_withdrawals'] + $summary['total_loan_disbursements'] + $summary['total_share_purchases'];
        $netCashFlow = $inflows - $outflows;

        return new self(
            memberId: $memberId,
            totalTransactions: $summary['total_transactions'],
            totalDeposits: $summary['total_deposits'],
            totalWithdrawals: $summary['total_withdrawals'],
            totalLoanDisbursements: $summary['total_loan_disbursements'],
            totalLoanRepayments: $summary['total_loan_repayments'],
            totalSharePurchases: $summary['total_share_purchases'],
            totalFees: $summary['total_fees'],
            netCashFlow: $netCashFlow,
            periodStart: $periodStart,
            periodEnd: $periodEnd
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'member_id' => $this->memberId,
            'period_start' => $this->periodStart?->format('Y-m-d'),
            'period_end' => $this->periodEnd?->format('Y-m-d'),
            'total_transactions' => $this->totalTransactions,
            'total_deposits' => $this->totalDeposits,
            'total_withdrawals' => $this->totalWithdrawals,
            'total_loan_disbursements' => $this->totalLoanDisbursements,
            'total_loan_repayments' => $this->totalLoanRepayments,
            'total_share_purchases' => $this->totalSharePurchases,
            'total_fees' => $this->totalFees,
            'net_cash_flow' => $this->netCashFlow,
        ];
    }

    /**
     * Get transaction type breakdown as percentages
     */
    public function getTypeBreakdown(): array
    {
        if ($this->totalTransactions == 0) {
            return [];
        }

        $typeCount = [];

        if ($this->totalDeposits > 0) $typeCount['deposits'] = 1;
        if ($this->totalWithdrawals > 0) $typeCount['withdrawals'] = 1;
        if ($this->totalLoanDisbursements > 0) $typeCount['loan_disbursements'] = 1;
        if ($this->totalLoanRepayments > 0) $typeCount['loan_repayments'] = 1;
        if ($this->totalSharePurchases > 0) $typeCount['share_purchases'] = 1;

        $totalTypes = array_sum($typeCount);

        return array_map(function($count) use ($totalTypes) {
            return round(($count / $totalTypes) * 100, 2);
        }, $typeCount);
    }

    /**
     * Check if member is primarily a saver or borrower
     */
    public function getMemberProfile(): string
    {
        $savingsActivity = $this->totalDeposits + $this->totalWithdrawals;
        $loanActivity = $this->totalLoanDisbursements + $this->totalLoanRepayments;

        if ($savingsActivity > $loanActivity * 2) {
            return 'saver';
        } elseif ($loanActivity > $savingsActivity * 2) {
            return 'borrower';
        } else {
            return 'balanced';
        }
    }
}
