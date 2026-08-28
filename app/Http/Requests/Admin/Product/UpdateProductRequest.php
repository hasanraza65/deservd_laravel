<?php

namespace App\Http\Requests\Admin\Product;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** See StoreProductRequest::prepareForValidation() — same admin-form/API dual contract. */
    protected function prepareForValidation(): void
    {
        foreach (['ingredients', 'allergens'] as $field) {
            if ($this->filled("{$field}_text")) {
                $this->merge([
                    $field => array_values(array_filter(array_map('trim', explode(',', $this->input("{$field}_text"))))),
                ]);
            }
        }
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],

            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],

            'weight' => ['nullable', 'string', 'max:30'],
            'protein_grams' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'carbohydrates_grams' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'fat_grams' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'fiber_grams' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'sugar_grams' => ['nullable', 'numeric', 'min:0', 'max:9999'],

            'ingredients' => ['nullable', 'array'],
            'ingredients.*' => ['string', 'max:100'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:100'],
            'storage_info' => ['nullable', 'string'],
            'shipping_info' => ['nullable', 'string'],

            'product_type' => ['sometimes', 'required', new Enum(ProductType::class)],
            'status' => ['sometimes', 'required', new Enum(ProductStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],

            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'low_stock_threshold' => ['sometimes', 'required', 'integer', 'min:0'],

            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
        ];
    }
}
