<?php

namespace App\Http\Requests\Admin\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', new Enum(OrderStatus::class)],
            'payment_status' => ['sometimes', 'required', new Enum(PaymentStatus::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('status') && !$this->filled('payment_status')) {
                $validator->errors()->add('status', 'Provide a status or payment_status to update.');
            }
        });
    }
}
