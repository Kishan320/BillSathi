<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BankTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes|required';

        return [
            'from_account_id' => [$required, 'exists:bank_accounts,id'],
            'to_account_id' => [$required, 'exists:bank_accounts,id', 'different:from_account_id'],
            'date' => [$required, 'date'],
            'amount' => [$required, 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
