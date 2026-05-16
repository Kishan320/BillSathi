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
        $requiredOnCreate = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];
        $accountTypeRule = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'account_type' => [...$accountTypeRule, 'string', 'in:cash,bank,credit_card,wallet'],
            'currency' => [...$requiredOnCreate, 'string', 'size:3'],
            'name' => [...$requiredOnCreate, 'string', 'max:255'],
            'opening_balance' => [$this->isMethod('post') ? 'nullable' : 'sometimes', 'numeric'],

            // Bank details (stored in metadata)
            'bank_name' => ['nullable', 'string', 'max:100', 'required_if:account_type,bank'],
            'account_holder_name' => ['nullable', 'string', 'max:100', 'required_if:account_type,bank'],
            'account_number' => ['nullable', 'string', 'max:50', 'required_if:account_type,bank'],
            'ifsc_swift_code' => ['nullable', 'string', 'max:30'],
            'branch_name' => ['nullable', 'string', 'max:100'],

            // Credit card details (stored in metadata)
            'last_4_digits' => ['nullable', 'digits:4', 'required_if:account_type,credit_card'],
            'billing_cycle_date' => ['nullable', 'integer', 'min:1', 'max:31', 'required_if:account_type,credit_card'],

            // Wallet details (stored in metadata)
            'wallet_provider_name' => ['nullable', 'string', 'max:100', 'required_if:account_type,wallet'],
        ];
    }
}
