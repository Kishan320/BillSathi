<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SettlementRequest extends FormRequest
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
            'date' => [...$required, 'date'],
            'amount' => [...$required, 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
