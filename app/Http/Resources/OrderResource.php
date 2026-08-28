<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'payment_status' => $this->payment_status->value,
            'fulfillment_status' => $this->fulfillment_status->value,

            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'shipping_cost' => $this->shipping_cost,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,

            'coupon_code' => $this->coupon_code,
            'shipping_method_name' => $this->shipping_method_name,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,

            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,

            'customer' => new UserResource($this->whenLoaded('user')),
            'standalone_items' => OrderItemResource::collection($this->whenLoaded('standaloneItems')),
            'box_groups' => OrderItemGroupResource::collection($this->whenLoaded('itemGroups')),

            'placed_at' => $this->placed_at,
            'created_at' => $this->created_at,
        ];
    }
}
