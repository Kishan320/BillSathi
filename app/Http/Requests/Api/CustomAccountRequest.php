<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CustomAccountRequest extends FormRequest
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
            'type' => ['in:asset,liability,equity,income,expense'],
            'opening_balance' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
        ];
    }
}
