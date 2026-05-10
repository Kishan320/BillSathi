<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [$this->isMethod('post') ? 'required' : 'sometimes', 'in:units,tax_categories,categories,stock_categories'],
            'value' => ['required', 'string', 'max:100'],
        ];
    }
}
