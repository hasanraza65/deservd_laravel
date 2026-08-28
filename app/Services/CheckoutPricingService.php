<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Exceptions\CheckoutValidationException;
use App\Exceptions\InvalidBoxSelectionException;
use App\Exceptions\InvalidCouponException;
use App\Models\BoxOption;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Recalculates a checkout entirely from the database. The frontend cart is
 * treated as a list of *intentions* (product ids, quantities, a box size and
 * its flavour breakdown) — every price, the coupon discount, shipping and tax
 * are derived here, never trusted from the request body.
 *
 * Call `price()` for a checkout preview (no locking, safe to call repeatedly
 * as a cart changes) and `priceForLockedProducts()` from inside the order
 * transaction once the same products are locked with lockForUpdate().
 */
class CheckoutPricingService
{
    public function __construct(private readonly CouponService $couponService)
    {
    }

    /**
     * @param  array<int, array{type: 'product', product_id: int, quantity: int}|array{type: 'box', box_size: int, selections: array<int, array{product_id: int, quantity: int}>}>  $items
     */
    public function price(array $items, ?string $couponCode, int $shippingMethodId, ?User $user): array
    {
        $productIds = $this->collectProductIds($items);
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        return $this->calculate($items, $products, $couponCode, $shippingMethodId, $user);
    }

    /** Same calculation, but against an already-locked product collection (see OrderService). */
    public function priceForLockedProducts(array $items, Collection $lockedProducts, ?string $couponCode, int $shippingMethodId, ?User $user): array
    {
        return $this->calculate($items, $lockedProducts, $couponCode, $shippingMethodId, $user);
    }

    public function collectProductIds(array $items): array
    {
        $ids = [];

        foreach ($items as $item) {
            if ($item['type'] === 'product') {
                $ids[] = (int) $item['product_id'];
            } else {
                foreach ($item['selections'] as $selection) {
                    $ids[] = (int) $selection['product_id'];
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /** Total quantity requested per product id, summed across standalone lines and every box's flavours. */
    public function quantityByProductId(array $items): array
    {
        $quantities = [];

        foreach ($items as $item) {
            if ($item['type'] === 'product') {
                $id = (int) $item['product_id'];
                $quantities[$id] = ($quantities[$id] ?? 0) + (int) $item['quantity'];
            } else {
                foreach ($item['selections'] as $selection) {
                    $id = (int) $selection['product_id'];
                    $quantities[$id] = ($quantities[$id] ?? 0) + (int) $selection['quantity'];
                }
            }
        }

        return $quantities;
    }

    private function calculate(array $items, Collection $products, ?string $couponCode, int $shippingMethodId, ?User $user): array
    {
        $subtotalCents = 0;
        $resolvedItems = [];

        foreach ($items as $item) {
            if ($item['type'] === 'product') {
                $product = $this->requireActiveProduct($products, (int) $item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPriceCents = $product->getRawOriginal('price');
                $lineTotal = $unitPriceCents * $quantity;

                $subtotalCents += $lineTotal;
                $resolvedItems[] = [
                    'type' => 'product',
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price_cents' => $unitPriceCents,
                    'total_price_cents' => $lineTotal,
                ];
            } else {
                $boxSize = (int) $item['box_size'];
                $boxOption = BoxOption::query()->active()->where('size', $boxSize)->first();
                if (!$boxOption) {
                    throw new CheckoutValidationException("{$boxSize} is not an available box size.");
                }

                $selections = [];
                $sumSelected = 0;
                foreach ($item['selections'] as $selection) {
                    $product = $this->requireActiveProduct($products, (int) $selection['product_id']);
                    $quantity = (int) $selection['quantity'];
                    $sumSelected += $quantity;
                    $selections[] = ['product' => $product, 'quantity' => $quantity];
                }

                // The customer's flavour picks must add up to exactly the box
                // size they chose — this is the one rule the frontend cannot
                // be trusted to have enforced correctly.
                if ($sumSelected !== $boxSize) {
                    throw new InvalidBoxSelectionException(
                        "Box selections must total exactly {$boxSize} cookies (got {$sumSelected})."
                    );
                }

                $boxPriceCents = $boxOption->getRawOriginal('price');
                $subtotalCents += $boxPriceCents;

                $resolvedItems[] = [
                    'type' => 'box',
                    'box_size' => $boxSize,
                    'price_cents' => $boxPriceCents,
                    'selections' => $selections,
                ];
            }
        }

        $shippingMethod = ShippingMethod::query()->active()->find($shippingMethodId);
        if (!$shippingMethod) {
            throw new CheckoutValidationException('Selected shipping method is not available.');
        }

        $discountCents = 0;
        $coupon = null;
        if ($couponCode) {
            $coupon = Coupon::query()->where('code', strtoupper(trim($couponCode)))->first();
            if (!$coupon) {
                throw new InvalidCouponException("Coupon \"{$couponCode}\" was not found.");
            }
            $discountCents = $this->couponService->validateAndCalculate($coupon, $subtotalCents, $user);
        }

        $shippingCents = $shippingMethod->getRawOriginal('price');
        $freeShippingThresholdCents = (int) Setting::get('free_shipping_threshold_cents', 4500);
        $taxableAfterDiscount = max(0, $subtotalCents - $discountCents);

        if ($shippingCents > 0 && $freeShippingThresholdCents > 0 && $taxableAfterDiscount >= $freeShippingThresholdCents) {
            $shippingCents = 0;
        }

        $taxRatePercent = (float) Setting::get('tax_rate_percent', 0);
        $taxCents = (int) round($taxableAfterDiscount * ($taxRatePercent / 100));

        $totalCents = max(0, $taxableAfterDiscount + $shippingCents + $taxCents);

        return [
            'items' => $resolvedItems,
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $discountCents,
            'shipping_cents' => $shippingCents,
            'tax_cents' => $taxCents,
            'total_cents' => $totalCents,
            'coupon' => $coupon,
            'shipping_method' => $shippingMethod,
        ];
    }

    private function requireActiveProduct(Collection $products, int $productId): Product
    {
        $product = $products->get($productId);

        if (!$product || $product->status !== ProductStatus::Active) {
            throw new CheckoutValidationException("Product #{$productId} is not available.");
        }

        return $product;
    }
}
