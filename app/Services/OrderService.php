<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\CheckoutValidationException;
use App\Exceptions\InsufficientStockException;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemGroup;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Order creation is the one place every "never trust the frontend" rule in
 * the spec converges: prices, stock, coupon rules and totals are all
 * recomputed here, inside a single database transaction, against product
 * rows locked with lockForUpdate() so two simultaneous checkouts can never
 * both succeed in overselling the same unit of stock.
 */
class OrderService
{
    public function __construct(
        private readonly CheckoutPricingService $pricingService,
        private readonly InventoryService $inventoryService,
    ) {
    }

    /**
     * @param  array  $data  Validated payload from StoreOrderRequest: items, coupon_code,
     *                       shipping_method_id, contact, shipping_address, billing_address, payment_method, notes.
     */
    public function createFromCheckout(array $data, ?User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $items = $data['items'];
            $quantityByProductId = $this->pricingService->quantityByProductId($items);
            $productIds = array_keys($quantityByProductId);

            // Lock every product touched by this order before pricing or
            // decrementing anything, so the stock check below is guaranteed
            // to still be true at the moment we commit.
            $lockedProducts = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantityByProductId as $productId => $quantity) {
                $product = $lockedProducts->get($productId);

                if (!$product) {
                    throw new CheckoutValidationException("Product #{$productId} is not available.");
                }

                if ($product->stock_quantity < $quantity) {
                    throw new InsufficientStockException($product->name, $quantity, $product->stock_quantity);
                }
            }

            $pricing = $this->pricingService->priceForLockedProducts(
                $items,
                $lockedProducts,
                $data['coupon_code'] ?? null,
                $data['shipping_method_id'],
                $user,
            );

            $order = Order::create([
                'order_number' => (string) Str::uuid(), // placeholder, replaced below once we have an id
                'user_id' => $user?->id,
                'status' => OrderStatus::New,
                'payment_status' => PaymentStatus::Paid, // no real payment gateway is integrated yet
                'fulfillment_status' => FulfillmentStatus::Unfulfilled,
                'subtotal' => $pricing['subtotal_cents'] / 100,
                'discount_amount' => $pricing['discount_cents'] / 100,
                'shipping_cost' => $pricing['shipping_cents'] / 100,
                'tax_amount' => $pricing['tax_cents'] / 100,
                'total' => $pricing['total_cents'] / 100,
                'coupon_id' => $pricing['coupon']?->id,
                'coupon_code' => $pricing['coupon']?->code,
                'shipping_method_id' => $pricing['shipping_method']->id,
                'shipping_method_name' => $pricing['shipping_method']->name,
                'payment_method' => $data['payment_method'] ?? 'card',
                'notes' => $data['notes'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'billing_address' => $data['billing_address'] ?? $data['shipping_address'] ?? null,
                'customer_email' => $data['contact']['email'] ?? $user?->email,
                'customer_phone' => $data['contact']['phone'] ?? $user?->phone,
                'placed_at' => now(),
            ]);

            $order->order_number = (string) (1047 + $order->id);
            $order->save();

            foreach ($pricing['items'] as $resolvedItem) {
                if ($resolvedItem['type'] === 'product') {
                    $this->createOrderItem($order, $resolvedItem['product'], $resolvedItem['quantity'], $resolvedItem['unit_price_cents'], null);
                } else {
                    $group = OrderItemGroup::create([
                        'order_id' => $order->id,
                        'type' => 'box',
                        'box_size' => $resolvedItem['box_size'],
                        'price' => $resolvedItem['price_cents'] / 100,
                    ]);

                    foreach ($resolvedItem['selections'] as $selection) {
                        $this->createOrderItem(
                            $order,
                            $selection['product'],
                            $selection['quantity'],
                            $selection['product']->getRawOriginal('price'),
                            $group,
                        );
                    }
                }
            }

            foreach ($quantityByProductId as $productId => $quantity) {
                $this->inventoryService->decrement(
                    $lockedProducts->get($productId),
                    $quantity,
                    'order',
                    $order,
                    $user,
                    "Order #{$order->order_number}",
                );
            }

            if ($pricing['coupon']) {
                $coupon = $pricing['coupon'];
                $coupon->increment('usage_count');
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $user?->id,
                    'order_id' => $order->id,
                    'discount_amount' => $pricing['discount_cents'] / 100,
                ]);
            }

            return $order->fresh(['items', 'itemGroups.items']);
        });
    }

    private function createOrderItem(Order $order, Product $product, int $quantity, int $unitPriceCents, ?OrderItemGroup $group): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'order_item_group_id' => $group?->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_image_path' => $product->images()->where('is_primary', true)->value('path'),
            'quantity' => $quantity,
            'unit_price' => $unitPriceCents / 100,
            'total_price' => ($unitPriceCents * $quantity) / 100,
            'metadata' => [
                'protein_grams' => $product->protein_grams,
                'weight' => $product->weight,
            ],
        ]);
    }
}
