<?php

namespace App\Http\Requests\Admin\Coupon;

use App\Enums\ActiveStatus;
use App\Enums\CouponType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id;
        $isPercentage = $this->input('type') === CouponType::Percentage->value;

        return [
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('coupons', 'code')->ignore($couponId)],
            'type' => ['required', new Enum(CouponType::class)],
            'value' => $isPercentage
                ? ['required', 'integer', 'min:1', 'max:100']
                : ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', new Enum(ActiveStatus::class)],
        ];
    }
}
