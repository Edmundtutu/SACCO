<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanRepaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'loan_id' => ['required', 'integer', 'exists:loans,id'],
            'amount' => [
                'required',
                'numeric',
                'min:' . config('sacco.minimum_repayment_amount', 1000),
                'max:' . config('sacco.maximum_transaction_amount', 10000000)
            ],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,mobile_money'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
    public function messages(): array
    {
        return [
            'loan_id.required' => 'Loan is required',
            'loan_id.exists' => 'Selected loan does not exist',
            'amount.required' => 'Payment amount is required',
            'amount.min' => 'Minimum payment amount is ' . config('sacco.minimum_repayment_amount', 1000),
            'amount.max' => 'Maximum transaction amount is ' . config('sacco.maximum_transaction_amount', 10000000),
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateLoanStatus();
            $this->validatePaymentAmount();
        });
    }

    protected function validateLoanStatus(): void
    {
        if (!$this->loan_id) {
            return;
        }

        $loan = \App\Models\Loan::find($this->loan_id);
        if (!$loan) {
            return;
        }

        if (!in_array($loan->status, ['disbursed', 'active'])) {
            $this->validator->errors()->add('loan_id', 'Loan is not active for repayment');
        }

        if ($loan->outstanding_balance <= 0) {
            $this->validator->errors()->add('loan_id', 'Loan is already fully paid');
        }
    }

    protected function validatePaymentAmount(): void
    {
        if (!$this->loan_id || !$this->amount) {
            return;
        }

        $loan = \App\Models\Loan::find($this->loan_id);
        if (!$loan) {
            return;
        }

        // Check if payment amount exceeds outstanding balance
        if ($this->amount > $loan->outstanding_balance) {
            $this->validator->errors()->add('amount',
                'Payment amount exceeds outstanding balance of ' . number_format($loan->outstanding_balance, 2)
            );
        }

        // Check minimum payment requirement
        $minPayment = $this->calculateMinimumPayment($loan);
        if ($this->amount < $minPayment) {
            $this->validator->errors()->add('amount',
                'Minimum payment required is ' . number_format($minPayment, 2)
            );
        }
    }

    protected function calculateMinimumPayment($loan): float
    {
        // This is a simplified calculation - we  might consider to implement more complex logic
        $minRepayment = config('sacco.minimum_repayment_amount', 1000);

        // Calculate based on loan terms if needed
        if ($loan->repayment_period_months > 0) {
            $monthlyPayment = $loan->principal_amount / $loan->repayment_period_months;
            return max($minRepayment, $monthlyPayment * 0.1); // 10% of monthly payment as minimum
        }

        return $minRepayment;
    }
}
