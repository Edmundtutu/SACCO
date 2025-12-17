<?php

namespace App\Http\Requests\V1\Improved;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'transactionType' => ['required', Rule::in(['Deposit','withdraw', 'LoanRepay'])],
            'amountTransacted' => ['required'],
            'dateOfTransaction'=> ['required'],
            'transactedById'=> ['requiured'],
            'accountId' => ['required'],
        ];
    }

    protected function prepareForValidation(){
        $this->merge([
            'transactionType' => $this->transaction_type,
            'dateOfTransaction'=> $this->Date_of_transaction,
            'transactedById'=> $this->member_id,
            'accountId' => $this->account_id,
        ]);
    }
}
