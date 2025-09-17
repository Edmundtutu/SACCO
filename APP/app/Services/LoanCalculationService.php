<?php

namespace App\Services;

use App\Models\Loan;

class LoanCalculationService
{
    /**
     * Calculate payment allocation between principal, interest, and penalties
     */
    public function calculatePaymentAllocation(Loan $loan, float $paymentAmount): array
    {
        // This is a simplified calculation - implement your business logic

        // Calculate accrued interest
        $accruedInterest = $this->calculateAccruedInterest($loan);

        // Calculate penalties if any
        $penalties = $this->calculatePenalties($loan);

        // Allocate payment: penalties first, then interest, then principal
        $allocation = [
            'penalty' => min($penalties, $paymentAmount),
            'interest' => 0,
            'principal' => 0,
        ];

        $remainingAmount = $paymentAmount - $allocation['penalty'];

        if ($remainingAmount > 0) {
            $allocation['interest'] = min($accruedInterest, $remainingAmount);
            $remainingAmount -= $allocation['interest'];

            if ($remainingAmount > 0) {
                $allocation['principal'] = min($loan->outstanding_balance, $remainingAmount);
            }
        }

        return $allocation;
    }

    /**
     * Calculate accrued interest on a loan
     */
    protected function calculateAccruedInterest(Loan $loan): float
    {
        if (!$loan->disbursement_date) {
            return 0;
        }

        $daysSinceDisbursement = now()->diffInDays($loan->disbursement_date);
        $annualInterestRate = $loan->interest_rate / 100;
        $dailyInterestRate = $annualInterestRate / 365;

        return $loan->outstanding_balance * $dailyInterestRate * $daysSinceDisbursement;
    }

    /**
     * Calculate penalties for overdue payments
     */
    protected function calculatePenalties(Loan $loan): float
    {
        // Implement penalty calculation based on your business rules
        // This is just a placeholder
        return 0;
    }

    /**
     * Generate loan repayment schedule
     */
    public function generateRepaymentSchedule(Loan $loan): array
    {
        $schedule = [];
        $monthlyPayment = $this->calculateMonthlyPayment($loan);
        $currentDate = $loan->disbursement_date ?? now();
        $remainingBalance = $loan->outstanding_balance;

        for ($i = 1; $i <= $loan->repayment_period_months; $i++) {
            $dueDate = $currentDate->copy()->addMonths($i);
            $interestPayment = ($remainingBalance * $loan->interest_rate / 100) / 12;
            $principalPayment = $monthlyPayment - $interestPayment;

            if ($principalPayment > $remainingBalance) {
                $principalPayment = $remainingBalance;
                $monthlyPayment = $principalPayment + $interestPayment;
            }

            $remainingBalance -= $principalPayment;

            $schedule[] = [
                'installment' => $i,
                'due_date' => $dueDate->format('Y-m-d'),
                'principal_amount' => round($principalPayment, 2),
                'interest_amount' => round($interestPayment, 2),
                'total_amount' => round($monthlyPayment, 2),
                'remaining_balance' => round($remainingBalance, 2),
            ];

            if ($remainingBalance <= 0) {
                break;
            }
        }

        return $schedule;
    }

    /**
     * Calculate monthly payment amount
     */
    protected function calculateMonthlyPayment(Loan $loan): float
    {
        if ($loan->repayment_period_months <= 0 || $loan->interest_rate <= 0) {
            return $loan->principal_amount / max(1, $loan->repayment_period_months);
        }

        $monthlyInterestRate = ($loan->interest_rate / 100) / 12;
        $numPayments = $loan->repayment_period_months;

        $monthlyPayment = $loan->principal_amount *
            ($monthlyInterestRate * pow(1 + $monthlyInterestRate, $numPayments)) /
            (pow(1 + $monthlyInterestRate, $numPayments) - 1);

        return round($monthlyPayment, 2);
    }
}
