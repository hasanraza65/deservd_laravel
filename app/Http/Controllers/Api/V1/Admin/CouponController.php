<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Coupon\CouponRequest;
use App\Http\Resources\Admin\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $coupons = Coupon::query()->latest()->paginate($request->integer('per_page', 20));

        return $this->success([
            'items' => CouponResource::collection($coupons->items()),
            'pagination' => [
                'current_page' => $coupons->currentPage(),
                'last_page' => $coupons->lastPage(),
                'total' => $coupons->total(),
            ],
        ]);
    }

    public function store(CouponRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);

        // 'value' must be set after 'type': the Coupon model's value mutator
        // reads $this->type to decide whether to store the raw percent or
        // convert dollars to cents (see Coupon::value()).
        $coupon = new Coupon();
        $coupon->fill(collect($data)->except('value')->all());
        $coupon->value = $data['value'];
        $coupon->save();

        // The DB default for usage_count (0) isn't reflected on the in-memory
        // instance we just inserted — refresh so the response is accurate.
        return $this->success(new CouponResource($coupon->fresh()), 'Coupon created successfully.', 201);
    }

    public function show(Coupon $coupon): JsonResponse
    {
        return $this->success(new CouponResource($coupon));
    }

    public function update(CouponRequest $request, Coupon $coupon): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper($data['code']);

        $coupon->fill(collect($data)->except('value')->all());
        $coupon->value = $data['value'];
        $coupon->save();

        return $this->success(new CouponResource($coupon->fresh()), 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $coupon->delete();

        return $this->success(null, 'Coupon deleted successfully.');
    }
}
