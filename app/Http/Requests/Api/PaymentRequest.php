<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'reference' => ['nullable', 'string', 'max:100'],
            'date' => [...$required, 'date'],
            'amount' => [...$required, 'numeric', 'min:0'],
            'type' => [$this->isMethod('post') ? 'nullable' : 'sometimes', 'in:sent,received'],
            'method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
