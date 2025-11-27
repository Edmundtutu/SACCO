<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SavingsGoal;
use App\Models\Account;

class StoreSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'target_amount' => ['required', 'numeric', 'min:1000'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date', 'after_or_equal:today'],
            'savings_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'status' => ['nullable', Rule::in(SavingsGoal::STATUSES)],
            'auto_nudge' => ['sometimes', 'boolean'],
            'nudge_frequency' => ['sometimes', Rule::in(SavingsGoal::NUDGE_FREQUENCIES)],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateSavingsAccountOwnership($validator);
        });
    }

    protected function validateSavingsAccountOwnership($validator): void
    {
        if (!$this->filled('savings_account_id')) {
            return;
        }

        $account = Account::find($this->input('savings_account_id'));

        if (!$account) {
            return;
        }

        if ((int) $account->member_id !== (int) auth()->id()) {
            $validator->errors()->add('savings_account_id', 'Selected savings account does not belong to the authenticated member.');
        }
    }

    protected function passedValidation(): void
    {
        if ($this->filled('current_amount') && $this->input('current_amount') > $this->input('target_amount')) {
            $this->merge([
                'current_amount' => $this->input('target_amount'),
            ]);
        }
    }
}
