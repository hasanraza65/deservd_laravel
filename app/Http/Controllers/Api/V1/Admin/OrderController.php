<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        // OrderResource nests the customer as a full UserResource, which
        // reads role/status (enum casts) — a partial select that omits them
        // silently nulls those attributes and crashes `$enum->value`.
        $query = Order::query()->with('user:id,first_name,last_name,email,role,status');

        if ($orderNumber = $request->query('order_number')) {
            $query->where('order_number', 'like', "%{$orderNumber}%");
        }

        if ($customer = $request->query('customer')) {
            $query->whereHas('user', fn ($q) => $q
                ->where('first_name', 'like', "%{$customer}%")
                ->orWhere('last_name', 'like', "%{$customer}%")
                ->orWhere('email', 'like', "%{$customer}%"));
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->query('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($from = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->success([
            'items' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['user', 'standaloneItems', 'itemGroups.items']);

        return $this->success(new OrderResource($order));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            $order->status = $data['status'];

            $newStatus = OrderStatus::from($data['status']);
            if (in_array($newStatus, [OrderStatus::Shipped, OrderStatus::Delivered], true)) {
                $order->fulfillment_status = FulfillmentStatus::Fulfilled;
            } elseif ($newStatus === OrderStatus::Preparing || $newStatus === OrderStatus::Baked || $newStatus === OrderStatus::Packaged) {
                $order->fulfillment_status = FulfillmentStatus::Processing;
            }
        }

        if (isset($data['payment_status'])) {
            $order->payment_status = $data['payment_status'];
        }

        $order->save();

        return $this->success(new OrderResource($order->fresh(['user', 'standaloneItems', 'itemGroups.items'])), 'Order updated successfully.');
    }
}
