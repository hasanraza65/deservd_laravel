<?php

namespace App\Models;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_status',
        'fulfillment_status',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'tax_amount',
        'total',
        'coupon_id',
        'coupon_code',
        'shipping_method_id',
        'shipping_method_name',
        'payment_method',
        'notes',
        'shipping_address',
        'billing_address',
        'customer_email',
        'customer_phone',
        'placed_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'fulfillment_status' => FulfillmentStatus::class,
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'placed_at' => 'datetime',
    ];

    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn (int $v) => Money::toDollars($v),
            set: fn (float|int $v) => Money::toCents($v),
        );
    }

    protected function discountAmount(): Attribute
    {
        return Attribute::make(
            get: fn (int $v) => Money::toDollars($v),
            set: fn (float|int $v) => Money::toCents($v),
        );
    }

    protected function shippingCost(): Attribute
    {
        return Attribute::make(
            get: fn (int $v) => Money::toDollars($v),
            set: fn (float|int $v) => Money::toCents($v),
        );
    }

    protected function taxAmount(): Attribute
    {
        return Attribute::make(
            get: fn (int $v) => Money::toDollars($v),
            set: fn (float|int $v) => Money::toCents($v),
        );
    }

    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn (int $v) => Money::toDollars($v),
            set: fn (float|int $v) => Money::toCents($v),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function itemGroups(): HasMany
    {
        return $this->hasMany(OrderItemGroup::class);
    }

    /** Order items that belong to no Build-a-Box group — plain product purchases. */
    public function standaloneItems(): HasMany
    {
        return $this->items()->whereNull('order_item_group_id');
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePaymentStatus(Builder $query, PaymentStatus $status): Builder
    {
        return $query->where('payment_status', $status);
    }
}
