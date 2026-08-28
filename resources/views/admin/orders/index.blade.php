<x-admin-layout title="Orders">
    <form method="GET" class="mb-5 flex flex-wrap gap-2">
        <input type="search" name="order_number" value="{{ request('order_number') }}" placeholder="Order #"
               class="w-32 rounded-md border border-cocoa-900/20 px-3 py-2 text-sm outline-none focus:border-cocoa-900">
        <input type="search" name="customer" value="{{ request('customer') }}" placeholder="Customer name or email"
               class="w-56 rounded-md border border-cocoa-900/20 px-3 py-2 text-sm outline-none focus:border-cocoa-900">
        <select name="status" class="rounded-md border border-cocoa-900/20 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            @foreach (['new','confirmed','preparing','baked','packaged','shipped','delivered','cancelled','refunded'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="rounded-md border border-cocoa-900/20 px-3 py-2 text-sm">
            <option value="">All Payments</option>
            @foreach (['pending','paid','failed','refunded'] as $status)
                <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-md border border-cocoa-900/20 px-3 py-2 text-sm">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-md border border-cocoa-900/20 px-3 py-2 text-sm">
        <x-btn type="submit" variant="outline">Filter</x-btn>
    </form>

    <div class="overflow-x-auto rounded-lg border border-cocoa-900/10 bg-cream-50">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="border-b border-cocoa-900/10 text-left text-xs font-bold uppercase tracking-wide text-cocoa-500">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Payment</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cocoa-900/8">
                @foreach ($orders as $order)
                    <tr>
                        <td class="px-4 py-3 font-bold text-cocoa-900">#{{ $order->order_number }}</td>
                        <td class="px-4 py-3 text-cocoa-700">{{ $order->user->name ?? $order->customer_email ?? 'Guest' }}</td>
                        <td class="px-4 py-3"><x-badge tone="pink">{{ $order->status->label() }}</x-badge></td>
                        <td class="px-4 py-3">
                            <x-badge :tone="$order->payment_status->value === 'paid' ? 'success' : ($order->payment_status->value === 'failed' ? 'danger' : 'warning')">
                                {{ $order->payment_status->value }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 font-bold">${{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3 text-cocoa-500">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-blush-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin-layout>
