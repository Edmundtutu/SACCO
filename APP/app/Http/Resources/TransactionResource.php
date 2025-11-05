<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'transaction_number' => $this->transaction_number,
            'member_id' => $this->member_id,
            'account_id' => $this->account_id,
            'type' => $this->type,
            'category' => $this->category,
            'amount' => $this->amount,
            'fee_amount' => $this->fee_amount,
            'net_amount' => $this->net_amount,
            'description' => $this->description,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'status' => $this->status,
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'transaction_date' => $this->transaction_date?->format('Y-m-d H:i:s'),
            'value_date' => $this->value_date?->format('Y-m-d H:i:s'),
            'related_loan_id' => $this->related_loan_id,
            'processed_by' => $this->processedBy?->name ?? 'System',
            'metadata' =>  is_string($this->metadata)
                ? json_decode($this->metadata, true)
                : null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),

            // Include member details when needed
            'member' => $this->whenLoaded('member', function () {
                return [
                    'id' => $this->member->id,
                    'name' => $this->member->name,
                    'email' => $this->member->email,
                ];
            }),

            // Include account details when needed
            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'account_number' => $this->account->account_number,
                    'balance' => $this->account->balance,
                ];
            }),

            // Include loan details when needed
            'loan' => $this->whenLoaded('loan', function () {
                return [
                    'id' => $this->loan->id,
                    'loan_number' => $this->loan->loan_number,
                    'outstanding_balance' => $this->loan->outstanding_balance,
                ];
            }),
        ];
    }
}
