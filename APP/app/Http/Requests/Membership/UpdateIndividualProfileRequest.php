<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIndividualProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'NIN' => $this->national_id,
            'DOB' => $this->date_of_birth,
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['sometimes', 'string', 'max:20'],
            'NIN' => ['sometimes', 'string', 'max:20'],
            'DOB' => ['sometimes', 'date'],
            'gender' => ['sometimes', 'in:male,female,other'],
            'occupation' => ['nullable', 'string'],
            'monthlyIncome' => ['nullable', 'numeric'],
            'nextOfKinName' => ['nullable', 'string'],
            'nextOfKinRelationship' => ['nullable', 'string'],
            'nextOfKinPhone' => ['nullable', 'string'],
            'nextOfKinAddress' => ['nullable', 'string'],
            'emergencyContactName' => ['nullable', 'string'],
            'emergencyContactPhone' => ['nullable', 'string'],
            'employerName' => ['nullable', 'string'],
            'employerAddress' => ['nullable', 'string'],
            'employerPhone' => ['nullable', 'string'],
            'bankName' => ['nullable', 'string'],
            'bankAccountNumber' => ['nullable', 'string'],
            'additionalNotes' => ['nullable', 'string'],
            'profileImage' => ['nullable', 'string'],
            'copyOfNationalID' => ['nullable', 'string'],
            'signature' => ['nullable', 'string'],
            'referee' => ['nullable', 'exists:users,id'],
        ];
    }
}
