<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoanRequest extends FormRequest
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
            'type' => ['in:given,taken'],
            'amount' => [...$required, 'numeric', 'min:0'],
            'interest_rate' => ['nullable', 'numeric', 'min:0'],
            'date' => [...$required, 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['in:active,closed,overdue'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
