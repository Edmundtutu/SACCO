<?php

namespace App\Http\Resources\Membership;

use Illuminate\Http\Resources\Json\JsonResource;

class IndividualProfileResource extends JsonResource
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
            'phone'=>$this->phone,
            'NIN'=>$this->national_id,
            'DOB'=>$this->date_of_birth,
            'gender'=>$this->gender,
            'occupation'=>$this->occupation,
            'monthlyIncome'=>$this->monthy_income,
            'nextOfKinName'=>$this->next_of_kin_name,
            'nextOfKinRelationship'=>$this->next_of_kin_relationship,
            'nextOfKinPhone'=>$this->next_of_kin_phone,
            'nextOfKinAddress'=>$this->next_of_kin_address,
            'emergencyContactName'=>$this->emergency_contact_name,
            'emergencyContactPhone'=>$this->emergency_contact_phone,
            'employerName'=>$this->employer_name,
            'employerAddress'=>$this->employer_address,
            'employerPhone'=>$this->employer_phone,
            'bankName'=>$this->bank_name,
            'bankAccountNumber'=>$this->bank_account_number,
            'additionalNotes'=>$this->additonal_notes,
            'profileImage'=>$this->profile_photo_path,
            'copyOfNationalID'=>$this->id_copy_path,
            'signature'=>$this->signature_path,
            'referee'=> $this->referee,
            'membership' => $this->whenLoaded('membership', function () {
                return [
                    'membership_id' => $this->membership->id,
                    'profile_type' => class_basename($this->membership->profile_type),                ];
            }),
        ];
    }
}
