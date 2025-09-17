<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{

    protected $availableBalance;

    public function __construct($resource, $availableBalance = null)
    {
        parent::__construct($resource);
        $this->availableBalance = $availableBalance;
    }

    public function toArray($request): array
    {
        return [
            'account_id' => $this->id,
            'account_number' => $this->account_number,
            'member_id' => $this->member_id,
            'current_balance' => $this->balance,
            'available_balance' => $this->availableBalance ?? $this->balance,
            'minimum_balance' => $this->savingsProduct->minimum_balance ?? 0,
            'interest_earned' => $this->interest_earned ?? 0,
            'last_transaction_date' => $this->updated_at?->format('Y-m-d H:i:s'),
            'account_status' => $this->status,
            'product_name' => $this->savingsProduct->name ?? 'Default Savings',
            'currency' => 'UGX', // Default currency

            'member' => [
                'id' => $this->member->id,
                'name' => $this->member->name,
                'email' => $this->member->email,
            ],
        ];
    }
}
