<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVslaProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function prepareForValidation():void
    {
        $this->merge([
            'subCounty' => $this->sub_county,
            'membershipCount'=>$this->membership_count,
            'registrationCertificate' => $this->registration_certificate,
            'constitutionCopy' => $this->constitution_copy,
            'resolutionMinutes' => $this->resolution_minutes,
            'executiveContacts' => $this->executive_contacts,
            'recommendationLc1' => $this->recommendation_lc1,
            'recommendationCdo' => $this->recommendation_cdo,
        ]);
    }

    public function rules(): array
    {
        return [
            'village' => ['sometimes', 'string'],
            'subCounty' => ['sometimes', 'string'],
            'district' => ['sometimes', 'string'],
            'membershipCount' => ['sometimes', 'integer'],
            'registrationCertificate' => ['sometimes', 'string'],
            'constitutionCopy' => ['sometimes', 'string'],
            'resolutionMinutes' => ['sometimes', 'string'],
            'executiveContacts' => ['sometimes', 'json'],
            'recommendationLc1' => ['sometimes', 'string'],
            'recommendationCdo' => ['sometimes', 'string'],
        ];
    }
}
