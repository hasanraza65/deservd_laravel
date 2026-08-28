<?php

namespace Database\Seeders;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

/**
 * DEMO DATA — synthetic orders so the admin dashboard, order list and
 * analytics have something to show locally. Built by calling the real
 * OrderService (the same code path a live checkout uses) rather than
 * inserting rows by hand, so seeded orders are guaranteed consistent with
 * actual order-creation logic and genuinely decrement stock.
 */
class DemoOrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::query()->count() > 0) {
            $this->command->info('Orders already exist, skipping demo order seeding.');

            return;
        }

        $customers = User::query()->customers()->inRandomOrder()->limit(10)->get();
        $standardProducts = Product::query()->where('product_type', 'standard')->pluck('id')->all();
        $shippingMethod = ShippingMethod::query()->where('type', 'shipping')->first();
        $pickupMethod = ShippingMethod::query()->where('type', 'pickup')->first();

        if ($customers->isEmpty() || empty($standardProducts) || !$shippingMethod) {
            $this->command->warn('Skipping demo orders: seed customers, products and shipping methods first.');

            return;
        }

        /** @var OrderService $orderService */
        $orderService = App::make(OrderService::class);

        $statusRotation = [
            OrderStatus::Delivered, OrderStatus::Delivered, OrderStatus::Shipped,
            OrderStatus::Packaged, OrderStatus::Preparing, OrderStatus::New, OrderStatus::Cancelled,
        ];

        $created = 0;

        foreach ($customers as $index => $customer) {
            $method = $index % 3 === 0 && $pickupMethod ? $pickupMethod : $shippingMethod;

            $items = $index % 2 === 0
                ? $this->randomBoxPayload($standardProducts)
                : $this->randomProductPayload($standardProducts);

            $payload = [
                'items' => $items,
                'shipping_method_id' => $method->id,
                'payment_method' => 'card',
                'contact' => ['name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone ?? '3055550100'],
                'shipping_address' => [
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'address_line1' => fake()->streetAddress(),
                    'city' => 'Miami',
                    'state' => 'FL',
                    'zip' => '33131',
                    'country' => 'US',
                ],
            ];

            $order = $orderService->createFromCheckout($payload, $customer);

            $status = $statusRotation[$index % count($statusRotation)];
            $order->status = $status;
            $order->fulfillment_status = match (true) {
                in_array($status, [OrderStatus::Shipped, OrderStatus::Delivered], true) => FulfillmentStatus::Fulfilled,
                in_array($status, [OrderStatus::Preparing, OrderStatus::Baked, OrderStatus::Packaged], true) => FulfillmentStatus::Processing,
                default => FulfillmentStatus::Unfulfilled,
            };
            $order->created_at = now()->subDays(random_int(0, 45));
            $order->save();

            $created++;
        }

        $this->command->info("Demo orders ready ({$created}).");
    }

    private function randomProductPayload(array $productIds): array
    {
        $picks = collect($productIds)->random(min(2, count($productIds)));

        return $picks->map(fn ($id) => [
            'type' => 'product',
            'product_id' => $id,
            'quantity' => random_int(1, 3),
        ])->values()->all();
    }

    private function randomBoxPayload(array $productIds): array
    {
        $boxSize = collect([4, 6, 8])->random();
        $flavourCount = min(3, count($productIds));
        $flavours = collect($productIds)->random($flavourCount)->values();

        $remaining = $boxSize;
        $selections = [];
        foreach ($flavours as $i => $productId) {
            $isLast = $i === $flavours->count() - 1;
            $qty = $isLast ? $remaining : random_int(1, max(1, intdiv($remaining, $flavours->count() - $i)));
            $qty = max(1, min($qty, $remaining));
            $selections[] = ['product_id' => $productId, 'quantity' => $qty];
            $remaining -= $qty;
        }

        // Any leftover from rounding goes onto the first flavour so the sum always equals the box size exactly.
        if ($remaining > 0) {
            $selections[0]['quantity'] += $remaining;
        }

        return [[
            'type' => 'box',
            'box_size' => $boxSize,
            'selections' => $selections,
        ]];
    }
}
