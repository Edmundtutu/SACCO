<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMfiProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation():void
    {
        $this->merge([
            'contactPerson' => $this->contact_person,
            'contactNumber' => $this->contact_number,
            'membershipCount' =>$this->membership_count,
            'boardMembers' => $this->board_members,
            'registrationCertificate' => $this->registration_certificate,
            'bylawsCopy' => $this->byLaws_copy,
            'resolutionMinutes' => $this->resolution_minutes,
            'operatingLicense' => $this->operating_license,
        ]);
    }

    public function rules(): array
    {
        return [
            'contactPerson' => ['sometimes', 'string'],
            'contactNumber' => ['sometimes', 'string'],
            'membershipCount' => ['sometimes', 'integer'],
            'boardMembers' => ['sometimes', 'json'],
            'registrationCertificate' => ['sometimes', 'string'],
            'bylawsCopy' => ['sometimes', 'string'],
            'resolutionMinutes' => ['sometimes', 'string'],
            'operatingLicense' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string'],
        ];
    }
}
