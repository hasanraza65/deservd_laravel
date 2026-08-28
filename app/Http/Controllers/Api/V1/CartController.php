<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CalculateCartRequest;
use App\Http\Resources\ShippingMethodResource;
use App\Services\CheckoutPricingService;
use App\Support\Money;
use Illuminate\Http\JsonResponse;

/**
 * There is no server-persisted cart — the frontend keeps cart contents in
 * localStorage, exactly as built. This endpoint takes that cart's contents
 * and returns the authoritative, server-computed price breakdown, so the
 * cart page and checkout summary can always show a real, trustworthy total
 * without the frontend doing any pricing math itself.
 */
class CartController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly CheckoutPricingService $pricingService)
    {
    }

    public function calculate(CalculateCartRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pricing = $this->pricingService->price(
            $data['items'],
            $data['coupon_code'] ?? null,
            $data['shipping_method_id'],
            // This route is public (a guest must see a live total too), so
            // it sits outside auth:sanctum — $request->user() would never
            // resolve a token here. See OrderController::store for the same pattern.
            auth('sanctum')->user(),
        );

        return $this->success([
            'subtotal' => Money::toDollars($pricing['subtotal_cents']),
            'discount' => Money::toDollars($pricing['discount_cents']),
            'shipping' => Money::toDollars($pricing['shipping_cents']),
            'tax' => Money::toDollars($pricing['tax_cents']),
            'total' => Money::toDollars($pricing['total_cents']),
            'coupon_applied' => $pricing['coupon']?->code,
            'shipping_method' => new ShippingMethodResource($pricing['shipping_method']),
        ]);
    }
}
