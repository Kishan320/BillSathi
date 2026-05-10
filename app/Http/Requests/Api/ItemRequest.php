<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nameRule = $this->isMethod('post') ? ['required'] : ['sometimes', 'required'];

        return [
            'item_type' => ['in:product,service,charge'],
            'manage_inventory' => ['nullable', 'in:0,1'],
            'serialized_product' => ['nullable', 'in:0,1'],
            'name' => [...$nameRule, 'string', 'max:255'],
            'hsn' => ['nullable', 'string', 'max:50'],
            'sku' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:50'],
            'tax_category' => ['nullable', 'string', 'max:100'],
            'stock_category' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'invoice_description' => ['nullable', 'string', 'max:4000'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price_type' => ['in:with_tax,without_tax'],
            'sale_discount' => [
                'nullable',
                'numeric',
                'min:0',
                $this->input('sale_discount_type') === 'percent' ? 'max:100' : 'max:999999999.99',
            ],
            'sale_discount_type' => ['in:percent,amount'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'opening_stock_qty' => ['nullable', 'numeric', 'min:0'],
            'opening_stock_cost' => ['nullable', 'numeric', 'min:0'],
            'serial_numbers' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $itemType = $this->input('item_type', 'product');

        if ($itemType === 'product') {
            return;
        }

        $normalized = [
            'manage_inventory' => 0,
            'serialized_product' => 0,
            'category' => null,
            'stock_category' => null,
            'purchase_price' => 0,
            'opening_stock_qty' => 0,
            'opening_stock_cost' => 0,
            'serial_numbers' => null,
        ];

        if ($itemType === 'charge') {
            $normalized = [
                ...$normalized,
                'unit' => 'Pcs',
                'tax_category' => null,
                'invoice_description' => null,
            ];
        }

        $this->merge($normalized);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('sale_discount_type') !== 'amount') {
                return;
            }

            $salePrice = (float) $this->input('sale_price', 0);
            $discount = (float) $this->input('sale_discount', 0);

            if ($discount > $salePrice) {
                $validator->errors()->add('sale_discount', 'Discount cannot be greater than the sales price.');
            }
        });
    }
}
