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
        $nameRule = $this->isMethod('post')
            ? ['required']
            : ['sometimes', 'required'];

        return [
            'name' => [
                ...$nameRule,
                'string',
                'min:2',
                'max:255',
            ],

            'gstin' => [
                'nullable',
                'string',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            ],

            'pan' => [
                'nullable',
                'string',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            ],

            'mobile' => [
                'nullable',
                'string',
                'regex:/^[6-9]\d{9}$/',
            ],

            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
            ],

            'type' => [
                'required',
                'in:customer,vendor,both',
            ],

            'due_in_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:365',
            ],

            'currency' => [
                'nullable',
                'string',
                'size:3',
            ],

            'billing_address1' => [
                'nullable',
                'string',
                'max:255',
            ],

            'billing_address2' => [
                'nullable',
                'string',
                'max:255',
            ],

            'billing_city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'billing_pincode' => [
                'nullable',
                'regex:/^[1-9][0-9]{5}$/',
            ],

            'billing_state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'billing_country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'shipping_same_as_billing' => [
                'nullable',
                'boolean',
            ],

            'shipping_address1' => [
                'nullable',
                'required_if:shipping_same_as_billing,0',
                'string',
                'max:255',
            ],

            'shipping_address2' => [
                'nullable',
                'string',
                'max:255',
            ],

            'shipping_city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'shipping_pincode' => [
                'nullable',
                'regex:/^[1-9][0-9]{5}$/',
            ],

            'shipping_state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'shipping_country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'opening_balance' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'opening_balance_type' => [
                'nullable',
                'required_with:opening_balance',
                'in:payable,receivable',
            ],

            'enable_customer_portal' => [
                'nullable',
                'boolean',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:250',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Contact name is required.',
            'name.min' => 'Contact name must be at least 2 characters.',
            'name.max' => 'Contact name may not be greater than 255 characters.',
            'gstin.regex' => 'Invalid GSTIN format (e.g. 22AAAAA0000A1Z5).',
            'pan.regex' => 'Invalid PAN format (e.g. ABCDE1234F).',
            'mobile.regex' => 'Invalid mobile number (must be 10 digits starting with 6-9).',
            'email.email' => 'Please enter a valid email address.',
            'type.required' => 'Contact type is required.',
            'type.in' => 'Contact type must be customer, vendor, or both.',
            'due_in_days.min' => 'Due in days must be at least 0.',
            'due_in_days.max' => 'Due in days may not exceed 365.',
            'currency.size' => 'Currency must be exactly 3 characters (e.g. INR, USD).',
            'billing_pincode.regex' => 'Invalid pincode (must be 6 digits).',
            'shipping_address1.required_if' => 'Shipping address is required when different from billing.',
            'shipping_pincode.regex' => 'Invalid shipping pincode (must be 6 digits).',
            'opening_balance.min' => 'Opening balance cannot be negative.',
            'opening_balance.max' => 'Opening balance exceeds maximum allowed value.',
            'opening_balance_type.required_with' => 'Balance type is required when opening balance is set.',
            'opening_balance_type.in' => 'Balance type must be payable or receivable.',
            'notes.max' => 'Notes may not exceed 250 characters.',
        ];
    }
}
