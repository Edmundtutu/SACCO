<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this-> id,
            'transactionType' => $this->transaction_type,
            'amountTransacted' => $this->amount,
            'dateOfTransaction'=> $this->Date_of_transaction,
            'transactedById'=> $this->member_id,
            'accountId' => $this->account_id,
        
        ];
    }
}
