<x-admin-layout title="Dashboard">

    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach (['today' => 'Today', '7_days' => '7 Days', '30_days' => '30 Days', 'this_month' => 'This Month', 'this_year' => 'This Year'] as $key => $label)
            <a
                href="{{ route('admin.dashboard', ['range' => $key]) }}"
                class="rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wide {{ $selectedRange === $key ? 'border-cocoa-900 bg-cocoa-900 text-cream-100' : 'border-cocoa-900/20 text-cocoa-700 hover:border-cocoa-900/50' }}"
            >{{ $label }}</a>
        @endforeach
    </div>

    {{-- All-time headline cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <x-stat-card label="Total Orders" :value="$summary['total_orders']" />
        <x-stat-card label="Total Revenue" value="${{ number_format($summary['total_revenue'], 2) }}" />
        <x-stat-card label="Total Customers" :value="$summary['total_customers']" />
        <x-stat-card label="Total Products" :value="$summary['total_products']" />
        <x-stat-card label="Pending Orders" :value="$summary['pending_orders']" />
        <x-stat-card label="Completed Orders" :value="$summary['completed_orders']" />
    </div>

    {{-- Range-filtered stats --}}
    <h2 class="mb-3 mt-8 text-xs font-bold uppercase tracking-wide text-cocoa-500">Selected range</h2>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card label="Orders" :value="$rangeStats['orders_count']" />
        <x-stat-card label="Revenue" value="${{ number_format($rangeStats['revenue'], 2) }}" />
        <x-stat-card label="Avg Order Value" value="${{ number_format($rangeStats['average_order_value'], 2) }}" />
        <x-stat-card label="Products Sold" :value="$rangeStats['products_sold']" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- Sales chart --}}
        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5 lg:col-span-2">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Sales Overview</h2>
            <canvas id="salesChart" height="110"></canvas>
        </div>

        {{-- Orders by status --}}
        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Orders by Status</h2>
            <div class="flex flex-col gap-2.5">
                @foreach ($ordersByStatus as $status => $count)
                    <div class="flex items-center justify-between text-sm">
                        <span class="capitalize text-cocoa-700">{{ str_replace('_', ' ', $status) }}</span>
                        <span class="font-display font-bold text-cocoa-900">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Best sellers --}}
        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Best Sellers</h2>
            @forelse ($bestSellers as $product)
                <div class="flex items-center justify-between border-b border-cocoa-900/8 py-2.5 text-sm last:border-0">
                    <span class="text-cocoa-800">{{ $product['product_name'] }}</span>
                    <span class="font-bold text-cocoa-900">{{ $product['units_sold'] }} sold</span>
                </div>
            @empty
                <p class="text-sm text-cocoa-500">No sales in this range yet.</p>
            @endforelse
        </div>

        {{-- Recent orders --}}
        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wide text-cocoa-500">Recent Orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-blush-600 hover:underline">View all</a>
            </div>
            @foreach ($recentOrders as $order)
                <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between border-b border-cocoa-900/8 py-2.5 text-sm last:border-0 hover:text-blush-600">
                    <span>#{{ $order->order_number }}</span>
                    <span class="font-bold">${{ number_format($order->total, 2) }}</span>
                </a>
            @endforeach
        </div>

        {{-- Recent customers --}}
        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wide text-cocoa-500">Recent Customers</h2>
                <a href="{{ route('admin.customers.index') }}" class="text-xs font-bold text-blush-600 hover:underline">View all</a>
            </div>
            @foreach ($recentCustomers as $customer)
                <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center justify-between border-b border-cocoa-900/8 py-2.5 text-sm last:border-0 hover:text-blush-600">
                    <span>{{ $customer->name }}</span>
                    <span class="text-cocoa-500">{{ $customer->created_at->diffForHumans() }}</span>
                </a>
            @endforeach
        </div>
    </div>

    @vite('resources/js/dashboard.js')
    <script>
        // dashboard.js is loaded as a module, which (like `defer`) runs after
        // parsing but before DOMContentLoaded — waiting for that event here
        // guarantees window.Chart is already set before this runs, regardless
        // of script order in the page.
        window.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('salesChart');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json(array_map(fn($d) => \Carbon\Carbon::parse($d['date'])->format('M j'), $salesByDate)),
                    datasets: [{
                        label: 'Revenue',
                        data: @json(array_map(fn($d) => $d['revenue'], $salesByDate)),
                        borderColor: '#d9556d',
                        backgroundColor: 'rgba(217, 85, 109, 0.1)',
                        tension: 0.3,
                        fill: true,
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: (v) => '$' + v } } },
                }
            });
        });
    </script>
</x-admin-layout>
