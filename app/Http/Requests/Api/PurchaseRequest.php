<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'contact_id'      => ['nullable', 'exists:vendors,id'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'vendor_name'     => ['nullable', 'string', 'max:255'],
            'warehouse'       => ['nullable', 'string', 'max:255'],
            'payment_terms'   => ['nullable', 'string', 'max:255'],
            'invoice_number'  => ['nullable', 'string', 'max:100'],
            'date'            => [...$required, 'date'],
            'due_date'        => ['nullable', 'date'],
            'subtotal'        => ['nullable', 'numeric', 'min:0'],
            'tax'             => ['nullable', 'numeric', 'min:0'],
            'total'           => [...$required, 'numeric', 'min:0'],
            'paid'            => ['nullable', 'numeric', 'min:0'],
            'status'          => ['in:draft,posted,paid,partial,overdue,cancelled'],
            'notes'           => ['nullable', 'string'],
            'items'           => ['nullable', 'array'],
            'items.*.item_id'    => ['nullable', 'exists:items,id'],
            'items.*.item_name'  => ['required_with:items', 'string', 'max:255'],
            'items.*.qty'        => ['required_with:items', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.discount'   => ['nullable', 'numeric', 'min:0'],
            'items.*.taxes'      => ['nullable', 'array'],
            'items.*.total'      => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}
