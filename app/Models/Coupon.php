<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use App\Enums\CouponType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'per_customer_limit',
        'usage_count',
        'status',
    ];

    protected $casts = [
        'type' => CouponType::class,
        'status' => ActiveStatus::class,
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected function minOrderAmount(): Attribute
    {
        return Attribute::make(
            get: fn (?int $v) => $v === null ? null : Money::toDollars($v),
            set: fn (null|float|int $v) => $v === null ? null : Money::toCents($v),
        );
    }

    protected function maxDiscount(): Attribute
    {
        return Attribute::make(
            get: fn (?int $v) => $v === null ? null : Money::toDollars($v),
            set: fn (null|float|int $v) => $v === null ? null : Money::toCents($v),
        );
    }

    /** Percentage coupons store a whole number 0-100; fixed coupons store dollars like other money fields. */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn (int $v) => $this->type === CouponType::Percentage ? $v : Money::toDollars($v),
            set: fn (float|int $v) => $this->type === CouponType::Percentage ? (int) $v : Money::toCents($v),
        );
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ActiveStatus::Active);
    }

    /** Rules that do not require order/customer context. Full validation lives in CouponService. */
    public function isCurrentlyActive(): bool
    {
        if ($this->status !== ActiveStatus::Active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}
