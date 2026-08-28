<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Exceptions\InvalidCouponException;
use App\Models\Coupon;
use App\Models\User;
use App\Support\Money;

class CouponService
{
    /**
     * Validates a coupon against order context and returns the discount in
     * cents. Throws with a customer-facing message on any rule violation —
     * never trust that the frontend already checked these.
     *
     * @param  int  $subtotalCents  Order subtotal before discount, in cents.
     */
    public function validateAndCalculate(Coupon $coupon, int $subtotalCents, ?User $user): int
    {
        if (!$coupon->isCurrentlyActive()) {
            throw new InvalidCouponException("Coupon \"{$coupon->code}\" is no longer active.");
        }

        $minOrderCents = $coupon->getRawOriginal('min_order_amount');
        if ($minOrderCents !== null && $subtotalCents < $minOrderCents) {
            $minDollars = Money::toDollars($minOrderCents);
            throw new InvalidCouponException("Coupon \"{$coupon->code}\" requires a minimum order of \${$minDollars}.");
        }

        if ($coupon->per_customer_limit !== null) {
            if (!$user) {
                throw new InvalidCouponException("Coupon \"{$coupon->code}\" requires an account.");
            }

            $usedByCustomer = $coupon->usages()->where('user_id', $user->id)->count();
            if ($usedByCustomer >= $coupon->per_customer_limit) {
                throw new InvalidCouponException("Coupon \"{$coupon->code}\" has already been used.");
            }
        }

        return $this->calculateDiscountCents($coupon, $subtotalCents);
    }

    private function calculateDiscountCents(Coupon $coupon, int $subtotalCents): int
    {
        $valueRaw = $coupon->getRawOriginal('value');

        $discount = $coupon->type === CouponType::Percentage
            ? (int) round($subtotalCents * ($valueRaw / 100))
            : $valueRaw;

        $maxDiscountCents = $coupon->getRawOriginal('max_discount');
        if ($maxDiscountCents !== null) {
            $discount = min($discount, $maxDiscountCents);
        }

        return min($discount, $subtotalCents);
    }
}
