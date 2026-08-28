<?php

namespace App\Http\Resources\Admin;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 *
 * Expects the controller to have annotated the model with
 * `orders_count` and `orders_sum_total` via withCount()/withSum() —
 * computed in SQL, never by loading every order into PHP.
 */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status->value,
            'email_verified_at' => $this->email_verified_at,
            'order_count' => $this->orders_count ?? 0,
            'total_spent' => Money::toDollars((int) ($this->orders_sum_total ?? 0)),
            'registered_at' => $this->created_at,
        ];
    }
}
