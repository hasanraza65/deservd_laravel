<?php

namespace App\Http\Requests\Admin\Product;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by the 'admin' route middleware
    }

    /**
     * The Blade admin form posts ingredients/allergens as a single
     * comma-separated text input (`ingredients_text`) rather than an array —
     * there's no JS array-builder widget in the admin UI. The JSON API sends
     * a real array and never sets these `_text` fields, so this is a no-op
     * for that path. Normalising here keeps one validation/storage contract
     * for both.
     */
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
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug', 'alpha_dash'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],

            'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:9999.99', 'gte:price'],

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

            'product_type' => ['required', new Enum(ProductType::class)],
            'status' => ['required', new Enum(ProductStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],

            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],

            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
