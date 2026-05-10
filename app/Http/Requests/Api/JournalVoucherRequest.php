<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class JournalVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes|required';

        return [
            'voucher_number' => ['nullable', 'string', 'max:100'],
            'date' => [$required, 'date'],
            'description' => ['nullable', 'string'],
            'debit' => [$required, 'numeric', 'min:0'],
            'credit' => [$required, 'numeric', 'min:0'],
        ];
    }
}
