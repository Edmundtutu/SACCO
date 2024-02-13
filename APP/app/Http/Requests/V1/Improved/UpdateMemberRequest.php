<?php

namespace App\Http\Requests\V1\Improved;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
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
        $method = $this->method();
        if($method == 'PUT'){
            $id = $this->route()->parameter('member');
            return [
                'firstname'=> ['required'],
                'lastname'=>['required'],
                'contact'=>['required'],
                'NIN'=>['required'],
                'dob'=>['required'],
                'joined'=>['required']
            ];
        }else{
            return [
                'firstname'=> ['sometimes','required'],
                'lastname'=>['sometimes','required'],
                'contact'=>['sometimes','required'],
                'NIN'=>['sometimes','required'],
                'dob'=>['sometimes','required'],
                'joined'=>['sometimes','required']
            ];
        } 
    }

    
}
