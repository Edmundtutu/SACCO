<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Calculate next payment date and amount
        $nextPaymentDate = null;
        $nextPaymentAmount = 0;

        if ($this->status === 'active' || $this->status === 'disbursed') {
            if ($this->first_payment_date) {
                $paymentsMade = $this->repayments ? $this->repayments->count() : 0;
                $nextPaymentDate = Carbon::parse($this->first_payment_date)->addMonths($paymentsMade)->format('Y-m-d');
                $nextPaymentAmount = $this->monthly_payment;
            }
        }

        // Calculate payments made and remaining
        $paymentsMade = $this->repayments ? $this->repayments->count() : 0;
        $paymentsRemaining = $this->repayment_period_months - $paymentsMade;

        // Get product name
        $productName = $this->loanProduct ? $this->loanProduct->name : 'Unknown Product';

        return [
            'id' => $this->id,
            'loan_number' => $this->loan_number,
            'product_name' => $productName,
            'principal_amount' => $this->principal_amount,
            'outstanding_balance' => $this->outstanding_balance,
            'interest_rate' => $this->interest_rate,
            'term_months' => $this->repayment_period_months,
            'monthly_payment' => $this->monthly_payment,
            'next_payment_date' => $nextPaymentDate,
            'next_payment_amount' => $nextPaymentAmount,
            'payments_made' => $paymentsMade,
            'payments_remaining' => $paymentsRemaining,
            'status' => $this->status,
            'application_date' => $this->application_date ? Carbon::parse($this->application_date)->format('Y-m-d') : null,
            'approval_date' => $this->approval_date ? Carbon::parse($this->approval_date)->format('Y-m-d') : null,
            'disbursement_date' => $this->disbursement_date ? Carbon::parse($this->disbursement_date)->format('Y-m-d') : null,
            'purpose' => $this->purpose,

            // Additional details
            'member_id' => $this->member_id,
            'loan_product_id' => $this->loan_product_id,
            'processing_fee' => $this->processing_fee,
            'insurance_fee' => $this->insurance_fee,
            'total_amount' => $this->total_amount,
            'first_payment_date' => $this->first_payment_date ? Carbon::parse($this->first_payment_date)->format('Y-m-d') : null,
            'maturity_date' => $this->maturity_date ? Carbon::parse($this->maturity_date)->format('Y-m-d') : null,
            'principal_balance' => $this->principal_balance,
            'interest_balance' => $this->interest_balance,
            'penalty_balance' => $this->penalty_balance,
            'total_paid' => $this->total_paid,
            'collateral_description' => $this->collateral_description,
            'collateral_value' => $this->collateral_value,
            'rejection_reason' => $this->rejection_reason,
            'approved_by' => $this->approved_by,
            'disbursed_by' => $this->disbursed_by,
            'disbursement_account_id' => $this->disbursement_account_id,

            // Related data
            'guarantors' => $this->whenLoaded('guarantors', function() {
                return $this->guarantors->map(function($guarantor) {
                    return [
                        'id' => $guarantor->id,
                        'guarantor_id' => $guarantor->guarantor_id,
                        'guarantor_name' => $guarantor->guarantor ? $guarantor->guarantor->name : 'Unknown',
                        'amount_guaranteed' => $guarantor->amount_guaranteed,
                        'status' => $guarantor->status,
                        'response_date' => $guarantor->response_date ? Carbon::parse($guarantor->response_date)->format('Y-m-d') : null
                    ];
                });
            }),

            'repayments' => $this->whenLoaded('repayments', function() {
                return $this->repayments->map(function($repayment) {
                    return [
                        'id' => $repayment->id,
                        'amount' => $repayment->amount,
                        'principal_amount' => $repayment->principal_amount,
                        'interest_amount' => $repayment->interest_amount,
                        'payment_date' => Carbon::parse($repayment->payment_date)->format('Y-m-d'),
                        'payment_method' => $repayment->payment_method,
                        'reference' => $repayment->reference
                    ];
                });
            }),

            'loan_product' => $this->whenLoaded('loanProduct', function() {
                return [
                    'id' => $this->loanProduct->id,
                    'name' => $this->loanProduct->name,
                    'description' => $this->loanProduct->description,
                    'min_amount' => $this->loanProduct->min_amount,
                    'max_amount' => $this->loanProduct->max_amount,
                    'interest_rate' => $this->loanProduct->interest_rate,
                    'max_term_months' => $this->loanProduct->max_term_months,
                    'processing_fee_rate' => $this->loanProduct->processing_fee_rate,
                    'insurance_rate' => $this->loanProduct->insurance_rate,
                    'guarantors_required' => $this->loanProduct->guarantors_required,
                    'collateral_required' => $this->loanProduct->collateral_required,
                    'requirements' => $this->loanProduct->requirements,
                    'is_active' => $this->loanProduct->is_active
                ];
            })
        ];
    }
}
