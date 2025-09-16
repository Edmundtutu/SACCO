<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoanDisbursementRequest extends FormRequest
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
            'disbursement_method' => ['required', 'string', 'in:cash,bank_transfer,mobile_money'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
    public function messages(): array
    {
        return [
            'loan_id.required' => 'Loan is required',
            'loan_id.exists' => 'Selected loan does not exist',
            'disbursement_method.required' => 'Disbursement method is required',
            'disbursement_method.in' => 'Invalid disbursement method',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateLoanStatus();
            $this->validateDisbursementAuthorization();
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

        if ($loan->status !== 'approved') {
            $this->validator->errors()->add('loan_id', 'Loan must be approved before disbursement');
        }

        // Check if loan has already been disbursed
        if (in_array($loan->status, ['disbursed', 'active', 'completed'])) {
            $this->validator->errors()->add('loan_id', 'Loan has already been disbursed');
        }

        // Check loan guarantors are all confirmed
        $pendingGuarantors = $loan->loanGuarantors()->where('status', 'pending')->count();
        if ($pendingGuarantors > 0) {
            $this->validator->errors()->add('loan_id', 'All loan guarantors must confirm before disbursement');
        }
    }

    protected function validateDisbursementAuthorization(): void
    {
        if (!$this->loan_id) {
            return;
        }

        $loan = \App\Models\Loan::find($this->loan_id);
        if (!$loan) {
            return;
        }

        // Check if user has permission to disburse loans
        if (!auth()->user()->can('disburse-loans')) {
            $this->validator->errors()->add('authorization', 'You are not authorized to disburse loans');
        }

        // Check loan amount limits for current user
        $userDisbursementLimit = auth()->user()->disbursement_limit ?? 0;
        if ($loan->principal_amount > $userDisbursementLimit) {
            $this->validator->errors()->add('loan_id',
                'Loan amount exceeds your disbursement limit of ' . number_format($userDisbursementLimit)
            );
        }
    }
}
