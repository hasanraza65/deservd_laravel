<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly DashboardAnalyticsService $analytics)
    {
    }

    public function index(Request $request): JsonResponse
    {
        [$from, $to] = $this->analytics->resolveRange(
            $request->query('range', '30_days'),
            $request->query('from'),
            $request->query('to'),
        );

        return $this->success([
            'summary' => $this->analytics->summary(),
            'range_stats' => $this->analytics->statsForRange($from, $to),
            'orders_by_status' => $this->analytics->ordersByStatus($from, $to),
            'sales_by_date' => $this->analytics->salesByDate($from, $to),
            'best_sellers' => $this->analytics->bestSellingProducts(5, $from, $to),
            'recent_orders' => OrderResource::collection($this->analytics->recentOrders()),
            'recent_customers' => UserResource::collection($this->analytics->recentCustomers()),
            'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
        ]);
    }
}
