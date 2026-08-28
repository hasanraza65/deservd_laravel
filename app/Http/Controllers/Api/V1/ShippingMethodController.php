<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;

/** Public, read-only — the checkout page needs to let a customer pick a method. */
class ShippingMethodController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $methods = ShippingMethod::query()->active()->orderBy('sort_order')->get();

        return $this->success(ShippingMethodResource::collection($methods));
    }
}
