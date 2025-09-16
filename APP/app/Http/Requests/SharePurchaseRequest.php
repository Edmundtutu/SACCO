<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SharePurchaseRequest extends FormRequest
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
            'amount' => [
                'required',
                'numeric',
                'min:' . config('sacco.share_value', 10000),
                'max:' . config('sacco.maximum_transaction_amount', 10000000)
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
    public function messages(): array
    {
        $shareValue = config('sacco.share_value', 10000);

        return [
            'member_id.required' => 'Member is required',
            'member_id.exists' => 'Selected member does not exist',
            'amount.required' => 'Purchase amount is required',
            'amount.min' => 'Minimum share purchase is ' . number_format($shareValue, 2),
            'amount.max' => 'Maximum transaction amount is ' . config('sacco.maximum_transaction_amount', 10000000),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateShareAmount();
            $this->validateMemberStatus();
            $this->validateDailyLimits();
        });
    }

    protected function validateShareAmount(): void
    {
        if (!$this->amount) {
            return;
        }

        $shareValue = config('sacco.share_value', 10000);

        if ($this->amount % $shareValue !== 0) {
            $this->validator->errors()->add('amount',
                'Share purchase amount must be in multiples of ' . number_format($shareValue, 2)
            );
        }

        $shareCount = $this->amount / $shareValue;
        $maxSharesPerPurchase = config('sacco.max_shares_per_purchase', 100);

        if ($shareCount > $maxSharesPerPurchase) {
            $this->validator->errors()->add('amount',
                'Maximum ' . $maxSharesPerPurchase . ' shares per transaction'
            );
        }
    }

    protected function validateMemberStatus(): void
    {
        if (!$this->member_id) {
            return;
        }

        $member = \App\Models\User::find($this->member_id);
        if (!$member) {
            return;
        }

        if ($member->status !== 'active') {
            $this->validator->errors()->add('member_id', 'Member account is not active');
        }

        $membership = $member->membership;
        if ($membership && $membership->approval_status !== 'approved') {
            $this->validator->errors()->add('member_id', 'Member is not fully approved for share purchases');
        }
    }

    protected function validateDailyLimits(): void
    {
        if (!$this->member_id || !$this->amount) {
            return;
        }

        $dailyLimit = config('sacco.daily_share_purchase_limit', 1000000);
        $todayPurchases = \App\Models\Transaction::where('member_id', $this->member_id)
            ->where('type', 'share_purchase')
            ->whereDate('transaction_date', now()->toDateString())
            ->where('status', 'completed')
            ->sum('amount');

        if (($todayPurchases + $this->amount) > $dailyLimit) {
            $this->validator->errors()->add('amount',
                'Daily share purchase limit of ' . number_format($dailyLimit) . ' exceeded'
            );
        }
    }
}
