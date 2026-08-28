<?php

namespace App\Http\Requests\Admin\ShippingMethod;

use App\Enums\ShippingMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', new Enum(ShippingMethodType::class)],
            'price' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'estimated_delivery_text' => ['nullable', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
