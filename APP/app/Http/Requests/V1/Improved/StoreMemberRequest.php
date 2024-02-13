<?php

namespace App\Http\Requests\V1\Improved;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
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

    protected function prepareForValidation(){
        $this->merge([

            'NIN' =>$this->ninno,
        
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'firstname'=> ['required'],
            'lastname'=>['required'],
            'contact'=>['required'],
            // 'NIN'=>['required'],
            'dob'=>['required'],
            'joined'=>['required']
        ];
    }
}
