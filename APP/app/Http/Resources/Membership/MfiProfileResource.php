<?php

namespace App\Http\Resources\Membership;

use Illuminate\Http\Resources\Json\JsonResource;

class MfiProfileResource extends JsonResource
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
            'contactPerson' => $this->contact_person,
            'contactNumber' => $this->contact_number,
            'membershipCount' => $this->membership_count,
            'boardMembers' => $this->board_members,
            'registrationCertificate' => $this->registration_certificate,
            'bylawsCopy' => $this->bylaws_copy,
            'resolutionMinutes' => $this->resolution_minutes,
            'operatingLicense' => $this->operating_license,
            'address' => $this->address,

            // Include membership info if loaded
            'membership' => $this->whenLoaded('membership', function () {
                return [
                    'membershipId' => $this->membership->id,
                    'profileType' => class_basename($this->membership->profile_type),
                ];
            }),
        ];
    }
}
