<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'id'=>$this->id,
            'firstname'=> $this->firstname,
            'lastname'=>$this->lastname,
            'contact'=>$this->contact,
            'NIN'=>$this->ninno,
            'dob'=>$this->dob,
            'joined'=>$this->joined

        ];
    }
}
