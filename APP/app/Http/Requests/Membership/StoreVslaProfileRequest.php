<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class StoreVslaProfileRequest extends FormRequest
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
            'village' => ['required', 'string'],
            'subCounty' => ['required', 'string'],
            'district' => ['required', 'string'],
            'membershipCount' => ['required', 'integer'],
            'registrationCertificate' => ['required', 'string'],
            'constitutionCopy' => ['required', 'string'],
            'resolutionMinutes' => ['required', 'string'],
            'executiveContacts' => ['required', 'json'],
            'recommendationLc1' => ['required', 'string'],
            'recommendationCdo' => ['required', 'string'],
        ];
    }
}
