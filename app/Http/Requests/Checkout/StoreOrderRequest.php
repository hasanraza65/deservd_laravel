<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the *shape* of a checkout submission only — product ids exist,
 * quantities are sane, a box's flavours sum to its size, addresses are
 * well-formed. It deliberately does NOT validate stock availability, coupon
 * eligibility, or compute any price: those require fresh, lock-protected
 * reads from the database and belong in CheckoutPricingService/OrderService,
 * not in a validation rule that runs against possibly-stale data.
 */
class StoreOrderRequest extends FormRequest
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
            'payment_method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'contact' => ['required', 'array'],
            'contact.name' => ['required', 'string', 'max:200'],
            'contact.email' => ['required', 'email', 'max:255'],
            'contact.phone' => ['required', 'string', 'max:30'],

            'shipping_address' => ['required', 'array'],
            'shipping_address.first_name' => ['required', 'string', 'max:100'],
            'shipping_address.last_name' => ['required', 'string', 'max:100'],
            'shipping_address.company' => ['nullable', 'string', 'max:150'],
            'shipping_address.address_line1' => ['required', 'string', 'max:255'],
            'shipping_address.address_line2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:100'],
            'shipping_address.state' => ['required', 'string', 'max:100'],
            'shipping_address.zip' => ['required', 'string', 'max:20'],
            'shipping_address.country' => ['sometimes', 'string', 'size:2'],
            'shipping_address.phone' => ['nullable', 'string', 'max:30'],

            'billing_address' => ['nullable', 'array'],
            'billing_address.first_name' => ['required_with:billing_address', 'string', 'max:100'],
            'billing_address.last_name' => ['required_with:billing_address', 'string', 'max:100'],
            'billing_address.address_line1' => ['required_with:billing_address', 'string', 'max:255'],
            'billing_address.city' => ['required_with:billing_address', 'string', 'max:100'],
            'billing_address.state' => ['required_with:billing_address', 'string', 'max:100'],
            'billing_address.zip' => ['required_with:billing_address', 'string', 'max:20'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                if (($item['type'] ?? null) !== 'box') {
                    continue;
                }

                $boxSize = (int) ($item['box_size'] ?? 0);
                $sum = collect($item['selections'] ?? [])->sum(fn ($s) => (int) ($s['quantity'] ?? 0));

                if ($sum !== $boxSize) {
                    $validator->errors()->add(
                        "items.{$index}.selections",
                        "Box selections must total exactly {$boxSize} cookies (got {$sum})."
                    );
                }
            }
        });
    }
}
