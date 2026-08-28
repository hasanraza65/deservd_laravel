<?php

namespace App\Models;

use App\Enums\ShippingMethodType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'type',
        'price',
        'estimated_delivery_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'type' => ShippingMethodType::class,
        'is_active' => 'boolean',
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => Money::toDollars($value),
            set: fn (float|int $value) => Money::toCents($value),
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
