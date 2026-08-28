<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItemGroup extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'box_size',
        'price',
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (int $v) => Money::toDollars($v),
            set: fn (float|int $v) => Money::toCents($v),
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
