<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShippingMethod\ShippingMethodRequest;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;

class ShippingMethodController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $methods = ShippingMethod::query()->orderBy('sort_order')->get();

        return $this->success(ShippingMethodResource::collection($methods));
    }

    public function store(ShippingMethodRequest $request): JsonResponse
    {
        $method = ShippingMethod::create($request->validated());

        return $this->success(new ShippingMethodResource($method->fresh()), 'Shipping method created successfully.', 201);
    }

    public function update(ShippingMethodRequest $request, ShippingMethod $shippingMethod): JsonResponse
    {
        $shippingMethod->update($request->validated());

        return $this->success(new ShippingMethodResource($shippingMethod->fresh()), 'Shipping method updated successfully.');
    }

    public function destroy(ShippingMethod $shippingMethod): JsonResponse
    {
        $shippingMethod->delete();

        return $this->success(null, 'Shipping method deleted successfully.');
    }
}
