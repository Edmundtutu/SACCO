<?php

namespace App\Http\Requests\V1\Improved;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // get the method from the request
        $method = $this->method();

        if($method=='PUT'){  // if the method isset to put or PATCH
            return [
                'accountno' => ['required'],
                'type' =>['required', Rule::in(['checkings','savings'])],
                'status'=> ['required', Rule::in(['Active', 'Inactive'])],
                'amount' => ['required'],
                'balance'=> ['required'],
                'acountHolder' => ['required']
            ];

        }elseif($method=='PATCH') {
            return [
                'accountno' => ['sometimes','required'],
                'type' =>['sometimes','required', Rule::in(['checkings','savings'])],
                'status'=> ['sometimes','required', Rule::in(['Active', 'Inactive'])],
                'amount' => ['sometimes','required'],
                'balance'=> ['sometimes','required'],
                'acountHolder' => ['sometimes','required']
            ];
        }

    }

    public function prepareForValidation(){
        $this->merge([
            // add a map of whatever formart in the request to that in the db
            'balance'=> $this->netamount,
            'acountHolder' => $this->member_id
        ]);
    }
}
