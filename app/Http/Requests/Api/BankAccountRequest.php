<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nameRule = $this->isMethod('post') ? 'required' : 'sometimes|required';

        return [
            'name' => [$nameRule, 'string', 'max:255'],
            'type' => ['in:bank,cash,credit_card,other'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'opening_balance' => [$this->isMethod('post') ? 'nullable' : 'sometimes', 'numeric'],
        ];
    }
}
