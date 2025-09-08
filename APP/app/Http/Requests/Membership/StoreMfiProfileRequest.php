<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class StoreMfiProfileRequest extends FormRequest
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
            'contactPerson' => ['nullable', 'string'],
            'contactNumber' => ['nullable', 'string'],
            'membershipCount' => ['nullable', 'integer'],
            'boardMembers' => ['nullable', 'json'],
            'registrationCertificate' => ['nullable', 'string'],
            'bylawsCopy' => ['nullable', 'string'],
            'resolutionMinutes' => ['nullable', 'string'],
            'operatingLicense' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
        ];
    }
}
