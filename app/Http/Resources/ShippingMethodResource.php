<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ShippingMethod */
class ShippingMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'price' => $this->price,
            'estimated_delivery_text' => $this->estimated_delivery_text,
            'is_active' => $this->is_active,
        ];
    }
}
