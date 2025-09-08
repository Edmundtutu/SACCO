<?php

namespace App\Http\Resources\Membership;

use Illuminate\Http\Resources\Json\JsonResource;

class VslaProfileResource extends JsonResource
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
            'village' => $this->village,
            'subCounty' => $this->sub_county,
            'district' => $this->district,
            'membershipCount' => $this->membership_count,
            'registrationCertificate' => $this->registration_certificate,
            'constitutionCopy' => $this->constitution_copy,
            'resolutionMinutes' => $this->resolution_minutes,
            'executiveContacts' => $this->executive_contacts,
            'recommendationLc1' => $this->recommendation_lc1,
            'recommendationCdo' => $this->recommendation_cdo,
            'membership' => $this->whenLoaded('membership', function () {
                return [
                    'membershipId' => $this->membership->id,
                    'profileType' => class_basename($this->membership->profile_type),
                ];
            }),
        ];
    }
}
