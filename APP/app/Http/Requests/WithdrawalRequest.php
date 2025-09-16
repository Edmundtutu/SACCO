<?php

namespace App\Http\Requests;

use App\Services\BalanceService;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
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
                'min:' . config('sacco.minimum_withdrawal_amount', 1000),
                'max:' . config('sacco.maximum_transaction_amount', 10000000)
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'requires_approval' => ['nullable', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return [
            'member_id.required' => 'Member is required',
            'member_id.exists' => 'Selected member does not exist',
            'account_id.required' => 'Account is required',
            'account_id.exists' => 'Selected account does not exist',
            'amount.required' => 'Withdrawal amount is required',
            'amount.min' => 'Minimum withdrawal amount is ' . config('sacco.minimum_withdrawal_amount', 1000),
            'amount.max' => 'Maximum transaction amount is ' . config('sacco.maximum_transaction_amount', 10000000),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateMemberAccount();
            $this->validateSufficientBalance();
            $this->validateDailyLimits();
            $this->validateWithdrawalRestrictions();
        });
    }

    protected function validateMemberAccount(): void
    {
        if (!$this->member_id || !$this->account_id) {
            return;
        }

        $account = \App\Models\Account::where('id', $this->account_id)
            ->where('member_id', $this->member_id)
            ->first();

        if (!$account) {
            $this->validator->errors()->add('account_id', 'Account does not belong to the selected member');
        }

        if ($account && $account->status !== 'active') {
            $this->validator->errors()->add('account_id', 'Account is not active');
        }
    }

    protected function validateSufficientBalance(): void
    {
        if (!$this->account_id || !$this->amount) {
            return;
        }

        $account = \App\Models\Account::find($this->account_id);
        if (!$account) {
            return;
        }

        $balanceService = app(BalanceService::class);
        $availableBalance = $balanceService->getAvailableBalance($account);

        if ($availableBalance < $this->amount) {
            $this->validator->errors()->add('amount',
                'Insufficient balance. Available: ' . number_format($availableBalance, 2)
            );
        }
    }

    protected function validateDailyLimits(): void
    {
        if (!$this->member_id || !$this->amount) {
            return;
        }

        $dailyLimit = config('sacco.daily_withdrawal_limit', 500000);
        $todayWithdrawals = \App\Models\Transaction::where('member_id', $this->member_id)
            ->where('type', 'withdrawal')
            ->whereDate('transaction_date', now()->toDateString())
            ->where('status', 'completed')
            ->sum('amount');

        if (($todayWithdrawals + $this->amount) > $dailyLimit) {
            $this->validator->errors()->add('amount', 'Daily withdrawal limit of ' . number_format($dailyLimit) . ' exceeded');
        }
    }

    protected function validateWithdrawalRestrictions(): void
    {
        if (!$this->account_id || !$this->amount) {
            return;
        }

        $account = \App\Models\Account::find($this->account_id);
        if (!$account || !$account->savingsProduct) {
            return;
        }

        // Check minimum balance after withdrawal
        $minBalance = $account->savingsProduct->minimum_balance ?? 0;
        if (($account->balance - $this->amount) < $minBalance) {
            $this->validator->errors()->add('amount',
                'Withdrawal would breach minimum balance requirement of ' . number_format($minBalance, 2)
            );
        }

        // Check withdrawal fee
        $withdrawalFee = $account->savingsProduct->withdrawal_fee ?? 0;
        if ($withdrawalFee > 0 && ($account->balance - $this->amount - $withdrawalFee) < $minBalance) {
            $this->validator->errors()->add('amount',
                'Insufficient balance to cover withdrawal fee of ' . number_format($withdrawalFee, 2)
            );
        }
    }
}
