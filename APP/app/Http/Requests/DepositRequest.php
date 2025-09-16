<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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
            'member_id' => ['required', 'integer', 'exists:users,id'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => [
                'required',
                'numeric',
                'min:' . config('sacco.minimum_deposit_amount', 1000),
                'max:' . config('sacco.maximum_transaction_amount', 10000000)
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }
    public function messages(): array
    {
        return [
            'member_id.required' => 'Member is required',
            'member_id.exists' => 'Selected member does not exist',
            'account_id.required' => 'Account is required',
            'account_id.exists' => 'Selected account does not exist',
            'amount.required' => 'Deposit amount is required',
            'amount.min' => 'Minimum deposit amount is ' . config('sacco.minimum_deposit_amount', 1000),
            'amount.max' => 'Maximum transaction amount is ' . config('sacco.maximum_transaction_amount', 10000000),
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation logic
            $this->validateMemberAccount();
            $this->validateDailyLimits();
        });
    }

    protected function validateMemberAccount(): void
    {
        if (!$this->member_id || !$this->account_id) {
            return;
        }

        // Check if account belongs to member
        $account = \App\Models\Account::where('id', $this->account_id)
            ->where('member_id', $this->member_id)
            ->first();

        if (!$account) {
            $this->validator->errors()->add('account_id', 'Account does not belong to the selected member');
        }

        // Check if account is active
        if ($account && $account->status !== 'active') {
            $this->validator->errors()->add('account_id', 'Account is not active');
        }
    }

    protected function validateDailyLimits(): void
    {
        if (!$this->member_id || !$this->amount) {
            return;
        }

        $dailyLimit = config('sacco.daily_deposit_limit', 1000000);
        $todayDeposits = \App\Models\Transaction::where('member_id', $this->member_id)
            ->where('type', 'deposit')
            ->whereDate('transaction_date', now()->toDateString())
            ->where('status', 'completed')
            ->sum('amount');

        if (($todayDeposits + $this->amount) > $dailyLimit) {
            $this->validator->errors()->add('amount', 'Daily deposit limit of ' . number_format($dailyLimit) . ' exceeded');
        }
    }
}
