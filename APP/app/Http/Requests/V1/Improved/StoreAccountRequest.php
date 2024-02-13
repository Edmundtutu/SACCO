<?php

namespace App\Http\Requests\V1\Improved;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
        return [
            'accountno' => ['required'],
            'type' =>['required', Rule::in(['checkings','Savings'])],
            'status'=> ['required', Rule::in(['Active', 'Inactive'])],
            'amount' => ['required'],
            'balance'=> ['required'],
            'acountHolder' => ['required']
        ];
    }

    protected function prepareForValidation(){
        $this->merge([
            'balance'=> $this->netamount,
            'acountHolder' => $this->member_id
        ]);
    }
}
