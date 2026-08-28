<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Same item/box shape as StoreOrderRequest, without the contact/address fields a pricing preview doesn't need. */
class CalculateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', Rule::in(['product', 'box'])],

            'items.*.product_id' => ['required_if:items.*.type,product', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_if:items.*.type,product', 'integer', 'min:1', 'max:99'],

            'items.*.box_size' => ['required_if:items.*.type,box', 'integer', Rule::exists('box_options', 'size')->where('is_active', true)],
            'items.*.selections' => ['required_if:items.*.type,box', 'array', 'min:1'],
            'items.*.selections.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.selections.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],

            'coupon_code' => ['nullable', 'string', 'max:50'],
            'shipping_method_id' => ['required', 'integer', Rule::exists('shipping_methods', 'id')->where('is_active', true)],
        ];
    }
}
