<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SavingsGoal;
use App\Models\Account;

class UpdateSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'target_amount' => ['sometimes', 'numeric', 'min:1000'],
            'current_amount' => ['sometimes', 'numeric', 'min:0'],
            'target_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'savings_account_id' => ['sometimes', 'nullable', 'integer', 'exists:accounts,id'],
            'status' => ['sometimes', Rule::in(SavingsGoal::STATUSES)],
            'auto_nudge' => ['sometimes', 'boolean'],
            'nudge_frequency' => ['sometimes', Rule::in(SavingsGoal::NUDGE_FREQUENCIES)],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateSavingsAccountOwnership($validator);
            $this->validateAmounts($validator);
        });
    }

    protected function validateSavingsAccountOwnership($validator): void
    {
        if (!$this->has('savings_account_id')) {
            return;
        }

        $accountId = $this->input('savings_account_id');
        if ($accountId === null) {
            return;
        }

        $account = Account::find($accountId);
        if (!$account) {
            return;
        }

        if ((int) $account->member_id !== (int) auth()->id()) {
            $validator->errors()->add('savings_account_id', 'Selected savings account does not belong to the authenticated member.');
        }
    }

    protected function validateAmounts($validator): void
    {
        if (!$this->hasAny(['target_amount', 'current_amount'])) {
            return;
        }

        $targetAmount = $this->input('target_amount', $this->route('savings_goal')?->target_amount);
        $currentAmount = $this->input('current_amount', $this->route('savings_goal')?->current_amount);

        if ($targetAmount !== null && $currentAmount !== null && (float) $currentAmount > (float) $targetAmount) {
            $validator->errors()->add('current_amount', 'Current amount cannot exceed target amount.');
        }
    }
}
