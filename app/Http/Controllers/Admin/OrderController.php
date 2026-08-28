<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
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

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'standaloneItems.product', 'itemGroups.items']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => ['sometimes', 'required', 'in:' . implode(',', array_column(OrderStatus::cases(), 'value'))],
            'payment_status' => ['sometimes', 'required', 'in:pending,paid,failed,refunded'],
        ]);

        if ($request->filled('status')) {
            $order->status = $request->input('status');

            $newStatus = OrderStatus::from($request->input('status'));
            $order->fulfillment_status = match (true) {
                in_array($newStatus, [OrderStatus::Shipped, OrderStatus::Delivered], true) => FulfillmentStatus::Fulfilled,
                in_array($newStatus, [OrderStatus::Preparing, OrderStatus::Baked, OrderStatus::Packaged], true) => FulfillmentStatus::Processing,
                default => $order->fulfillment_status,
            };
        }

        if ($request->filled('payment_status')) {
            $order->payment_status = $request->input('payment_status');
        }

        $order->save();

        return back()->with('status', 'Order updated successfully.');
    }
}
