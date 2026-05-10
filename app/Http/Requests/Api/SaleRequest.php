<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes|required';

        return [
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'date' => [$required, 'date'],
            'due_date' => ['nullable', 'date'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'total' => [$required, 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'status' => ['in:draft,pending,paid,overdue,cancelled'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
