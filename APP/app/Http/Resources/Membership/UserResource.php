<?php

namespace App\Http\Resources\Membership;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name'=>$this->name,
            'email'=>$this->email,
            'role'=>$this->role,
            'status'=>$this->status,
            'membershipDate'=>$this->membership_date,
            'accountVerifiedDate'=>$this->account_verified_at,
            'password'=>$this->password

        ];
    }
}
