<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardAnalyticsService $analytics)
    {
    }

    public function index(Request $request): View
    {
        $range = $request->query('range', '30_days');
        [$from, $to] = $this->analytics->resolveRange($range, $request->query('from'), $request->query('to'));

        return view('admin.dashboard', [
            'summary' => $this->analytics->summary(),
            'rangeStats' => $this->analytics->statsForRange($from, $to),
            'ordersByStatus' => $this->analytics->ordersByStatus($from, $to),
            'salesByDate' => $this->analytics->salesByDate($from, $to),
            'bestSellers' => $this->analytics->bestSellingProducts(5, $from, $to),
            'recentOrders' => $this->analytics->recentOrders(8),
            'recentCustomers' => $this->analytics->recentCustomers(6),
            'selectedRange' => $range,
        ]);
    }
}
