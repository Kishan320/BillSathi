<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nameRule = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$nameRule, 'string', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'type' => ['in:customer,vendor,both'],
            'due_in_days' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'billing_address1' => ['nullable', 'string', 'max:255'],
            'billing_address2' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:100'],
            'billing_pincode' => ['nullable', 'string', 'max:20'],
            'billing_state' => ['nullable', 'string', 'max:100'],
            'billing_country' => ['nullable', 'string', 'max:100'],
            'shipping_same_as_billing' => ['nullable', 'in:0,1'],
            'shipping_address1' => ['nullable', 'string', 'max:255'],
            'shipping_address2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_pincode' => ['nullable', 'string', 'max:20'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'opening_balance_type' => ['in:payable,receivable'],
            'enable_customer_portal' => ['nullable', 'in:0,1'],
            'notes' => ['nullable', 'string', 'max:250'],
        ];
    }
}
