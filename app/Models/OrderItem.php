<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'order_item_group_id',
        'product_name',
        'product_sku',
        'product_image_path',
        'quantity',
        'unit_price',
        'total_price',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn (int $v) => Money::toDollars($v),
            set: fn (float|int $v) => Money::toCents($v),
        );
    }

    protected function totalPrice(): Attribute
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(OrderItemGroup::class, 'order_item_group_id');
    }
}
