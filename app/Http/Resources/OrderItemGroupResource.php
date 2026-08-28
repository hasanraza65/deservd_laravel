<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderItemGroup */
class OrderItemGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'box_size' => $this->box_size,
            'price' => $this->price,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
