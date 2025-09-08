<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;


class StoreIndividualProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust as needed
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'NIN' => $this->national_id,
            'DOB' => $this->date_of_birth,
            'monthlyIncome' => $this->monthy_income,
            'nextOfKinName' => $this->next_of_kin_name,
            'nextOfKinRelationship' => $this->next_of_kin_relationship,
            'nextOfKinPhone' => $this->next_of_kin_phone,
            'nextOfKinAddress' => $this->next_of_kin_address,
            'emergencyContactName' => $this->emergency_contact_name,
            'emergencyContactPhone' => $this->emergency_contact_phone,
            'employerName' => $this->employer_name,
            'employerAddress' => $this->employer_address,
            'employerPhone' => $this->employer_phone,
            'bankName' => $this->bank_name,
            'bankAccountNumber' => $this->bank_account_number,
            'additionalNotes' => $this->additonal_notes,
            'profileImage' => $this->profile_photo_path,
            'copyOfNationalID' => $this->id_copy_path,
            'signature' => $this->signature_path,
        ]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'NIN' => ['required', 'string', 'max:20'],
            'DOB' => ['required', 'date'],
            'gender' => ['required', 'in:male,female,other'],
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
