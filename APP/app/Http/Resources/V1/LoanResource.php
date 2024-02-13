<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
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
            'id'=> $this->id,
            'loanType' => $this->loan_type,
            'DOA' => $this->DOA,
            'DOR' => $this->DOR,
            'loanAmount' => $this->loan_amount,
            'intrestRate'=> $this->intrest_rate,
            'loanStatus' => $this-> loan_status,
            'repaymentTerms'=> $this-> repayment_terms,
            'loanOwnerId'=> $this->member_id,
            'loanAccountId' => $this->account_id
        ];
    }
}
