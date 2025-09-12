<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ShareResource extends JsonResource
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
            'id' => $this->id,
            'total_shares' => $this->total_shares,
            'share_value' => $this->share_value,
            'total_value' => $this->total_value,
            'dividends_earned' => $this->dividends_earned,
            'last_dividend_date' => $this->last_dividend_date,
            'certificates' => $this->certificates ? $this->certificates->map(function($certificate) {
                return [
                    'id' => $certificate->id,
                    'certificate_number' => $certificate->certificate_number,
                    'shares_count' => $certificate->shares_count,
                    'purchase_date' => $certificate->purchase_date,
                    'purchase_price' => $certificate->purchase_price,
                ];
            }) : [],
        ];
    }
}
