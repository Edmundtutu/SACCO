<?php

namespace App\Http\V1\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLoanRequest extends FormRequest
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
        $method =$this->method();
        if($method=="PUT"){
            return [
                'loanType' => ['required', Rule::in(['Team', 'Persoanl', 'Bussiness'])],
                'loanAmount' =>['required'],
                'intrestRate'=>['required'],
                'loanStatus' => ['required', Rule::in(['Paid','Active', 'Defualt'])],
                'repaymentTerms'=> ['required'],
                'loanOwnerId'=>['required'],
                'loanAccountId' =>['required']
            ];
        }else{
            return [
                'loanType' => ['sometimes','required', Rule::in(['Team', 'Persoanl', 'Bussiness'])],
                'loanAmount' =>['sometimes','required'],
                'intrestRate'=>['sometimes','required'],
                'loanStatus' => ['sometimes','required', Rule::in(['Paid','Active', 'Defualt'])],
                'repaymentTerms'=> ['sometimes','required'],
                'loanOwnerId'=>['sometimes','required'],
                'loanAccountId' =>['sometimes','required']
            ]; 
        }
        
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
