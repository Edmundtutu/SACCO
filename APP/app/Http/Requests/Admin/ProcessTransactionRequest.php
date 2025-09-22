<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', Rule::in([
                'deposit', 'withdrawal', 'share_purchase', 
                'loan_disbursement', 'loan_repayment'
            ])],
            'amount' => ['required', 'numeric', 'min:1'],
            'account_id' => ['required_if:type,deposit,withdrawal', 'integer', 'exists:accounts,id'],
            'related_loan_id' => ['required_if:type,loan_disbursement,loan_repayment', 'integer', 'exists:loans,id'],
            'fee_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Member selection is required.',
            'member_id.exists' => 'Selected member does not exist.',
            'type.required' => 'Transaction type is required.',
            'type.in' => 'Invalid transaction type selected.',
            'amount.required' => 'Transaction amount is required.',
            'amount.min' => 'Transaction amount must be greater than zero.',
            'account_id.required_if' => 'Account selection is required for savings transactions.',
            'account_id.exists' => 'Selected account does not exist.',
            'related_loan_id.required_if' => 'Loan selection is required for loan transactions.',
            'related_loan_id.exists' => 'Selected loan does not exist.',
            'fee_amount.min' => 'Fee amount cannot be negative.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'member_id' => 'member',
            'type' => 'transaction type',
            'amount' => 'transaction amount',
            'account_id' => 'account',
            'related_loan_id' => 'loan',
            'fee_amount' => 'fee amount',
            'description' => 'transaction description',
        ];
    }
}
