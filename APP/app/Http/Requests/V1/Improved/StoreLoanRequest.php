<?php

namespace App\Http\Requests\V1\Improved;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
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
            'loanType' => ['required', Rule::in(['Team', 'Persoanl', 'Bussiness'])],
            'loanAmount' =>['required'],
            'intrestRate'=>['required'],
            'loanStatus' => ['required', Rule::in(['Paid','Active', 'Defualt'])],
            'repaymentTerms'=> ['required'],
            'loanOwnerId'=>['required'],
            'loanAccountId' =>['required']
        ];
    }
    protected function prepareForValidation(){
        $this->merge([
            'loanType'=>$this->loan_type,
            'loanAmount'=>$this->loan_amount,
            'intrestRate'=>$this->intrest_rate,
            'loanStatus' =>$this->loan_status,
            'repaymentTerms'=>$this->repayment_terms,
            'loanOwnerId'=>$this->member_id,
            'loanAccountId'=>$this->account_id
        ]);
    }
}
