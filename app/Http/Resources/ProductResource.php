<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,

            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,

            'weight' => $this->weight,
            'protein_grams' => $this->protein_grams,
            'calories' => $this->calories,
            'carbohydrates_grams' => $this->carbohydrates_grams,
            'fat_grams' => $this->fat_grams,
            'fiber_grams' => $this->fiber_grams,
            'sugar_grams' => $this->sugar_grams,

            'ingredients' => $this->ingredients ?? [],
            'allergens' => $this->allergens ?? [],
            'storage_info' => $this->storage_info,
            'shipping_info' => $this->shipping_info,

            'product_type' => $this->product_type->value,
            'product_type_label' => $this->product_type->label(),
            'status' => $this->status->value,
            'is_featured' => $this->is_featured,

            'stock_quantity' => $this->stock_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'in_stock' => $this->isInStock(),
            'low_stock' => $this->isLowStock(),

            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
