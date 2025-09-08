<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    // in order to map the request with the data_fields in the db
    protected function prepareForValidation(){
        $this->merge([
            'membershipDate'=>$this->membership_date,
            'accountVerifiedDate'=>$this->account_verified_at,

        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $method = $this->method();
        if($method == 'PUT'){
            $id = $this->route()->parameter('user');
            return [
                'name' => ['required','string','max:255'],
                'email' => ['required','string','email','max:255','unique:users'],
                'password' => ['required','string','min:8','confirmed'],
                'role' => ['required','in:admin,member,staff_level_1,staff_level_2,staff_level_3'],
                'status' => ['required','in:active,inactive,suspended,pending_approval'],
                'membership_date'     => ['nullable','date'],
                'account_verified_at' => ['nullable','date'],
            ];
        }else{
            return [
                'name' => ['sometimes','string','max:255'],
                'email' => ['sometimes','string','email','max:255','unique:users'],
                'password' => ['sometimes','string','min:8','confirmed'],
                'role' => ['sometimes','in:admin,member,staff_level_1,staff_level_2,staff_level_3'],
                'status' => ['sometimes','in:active,inactive,suspended,pending_approval'],
                'membership_date'     => ['nullable','date'],
                'account_verified_at' => ['nullable','date'],
            ];
        }
    }


}
