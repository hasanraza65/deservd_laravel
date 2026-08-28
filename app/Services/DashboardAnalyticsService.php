<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Every figure here comes from a SQL aggregate (COUNT/SUM/AVG/GROUP BY) —
 * never from pulling rows into PHP and summing in a loop, which stops
 * scaling the moment the orders table gets large.
 */
class DashboardAnalyticsService
{
    /** Headline cards: all-time plus "right now" operational counts. */
    public function summary(): array
    {
        $paidTotalCents = (int) Order::query()->where('payment_status', PaymentStatus::Paid->value)->sum('total');

        return [
            'total_orders' => Order::query()->count(),
            'total_revenue' => Money::toDollars($paidTotalCents),
            'total_customers' => User::query()->customers()->count(),
            'total_products' => Product::query()->count(),
            'pending_orders' => Order::query()->whereNotIn('status', [
                OrderStatus::Delivered->value, OrderStatus::Cancelled->value, OrderStatus::Refunded->value,
            ])->count(),
            'completed_orders' => Order::query()->where('status', OrderStatus::Delivered->value)->count(),
        ];
    }

    /**
     * Deliberately not column-restricted: OrderResource/UserResource read
     * several attributes each (including enum-cast ones like status,
     * payment_status, fulfillment_status, role) — a partial select silently
     * nulls out any column left off the list, which breaks `$enum->value`
     * with a hard-to-read "on null" error. Limiting to `$limit` rows already
     * keeps this cheap; there is no full-table scan being avoided here.
     */
    public function recentOrders(int $limit = 10)
    {
        return Order::query()
            ->with('user:id,first_name,last_name,email,role,status')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function recentCustomers(int $limit = 10)
    {
        return User::query()
            ->customers()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** Best sellers by units sold, computed in SQL via a join + group by. */
    public function bestSellingProducts(int $limit = 5, ?CarbonInterface $from = null, ?CarbonInterface $to = null)
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->when($from, fn ($q) => $q->where('orders.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('orders.created_at', '<=', $to))
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc(DB::raw('SUM(order_items.quantity)'))
            ->limit($limit)
            ->get([
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.total_price) as revenue_cents'),
            ])
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'units_sold' => (int) $row->units_sold,
                'revenue' => Money::toDollars((int) $row->revenue_cents),
            ]);
    }

    public function ordersByStatus(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $counts = Order::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->groupBy('status')
            ->pluck(DB::raw('COUNT(*)'), 'status');

        return collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $status) => [$status->value => (int) ($counts[$status->value] ?? 0)])
            ->all();
    }

    /** Day-by-day revenue and order count for a date range, for the sales-overview chart. */
    public function salesByDate(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = Order::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue_cents'),
            ]);

        return $rows->map(fn ($row) => [
            'date' => (string) $row->date,
            'orders' => (int) $row->orders_count,
            'revenue' => Money::toDollars((int) $row->revenue_cents),
        ])->all();
    }

    /** Revenue, order count and average order value for an arbitrary date range — the shared building block for date-filtered stats. */
    public function statsForRange(CarbonInterface $from, CarbonInterface $to): array
    {
        $row = Order::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total), 0) as revenue_cents, COALESCE(AVG(total), 0) as avg_cents')
            ->first();

        $productsSold = (int) OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->whereBetween('orders.created_at', [$from, $to])
            ->sum('order_items.quantity');

        return [
            'orders_count' => (int) $row->orders_count,
            'revenue' => Money::toDollars((int) $row->revenue_cents),
            'average_order_value' => Money::toDollars((int) round($row->avg_cents)),
            'products_sold' => $productsSold,
            'new_customers' => User::query()->customers()->whereBetween('created_at', [$from, $to])->count(),
        ];
    }

    /** Resolves the named quick-filters used by the admin dashboard's date picker. */
    public function resolveRange(string $preset, ?string $customFrom = null, ?string $customTo = null): array
    {
        $now = Carbon::now();

        return match ($preset) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            '7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom' => [
                $customFrom ? Carbon::parse($customFrom)->startOfDay() : $now->copy()->subDays(29)->startOfDay(),
                $customTo ? Carbon::parse($customTo)->endOfDay() : $now->copy()->endOfDay(),
            ],
            default => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
