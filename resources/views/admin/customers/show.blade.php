<x-admin-layout :title="$customer->name">
    <a href="{{ route('admin.customers.index') }}" class="mb-4 inline-block text-sm text-cocoa-600 hover:text-blush-600">&larr; Back to customers</a>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-6 grid grid-cols-3 gap-4">
                <x-stat-card label="Orders" :value="$customer->orders_count" />
                <x-stat-card label="Total Spent" value="${{ number_format(($customer->orders_sum_total ?? 0) / 100, 2) }}" />
                <x-stat-card label="Since" :value="$customer->created_at->format('M Y')" />
            </div>

            <h2 class="mb-3 text-xs font-bold uppercase tracking-wide text-cocoa-500">Order History</h2>
            <div class="overflow-x-auto rounded-lg border border-cocoa-900/10 bg-cream-50">
                <table class="w-full text-sm">
                    <thead class="border-b border-cocoa-900/10 text-left text-xs font-bold uppercase tracking-wide text-cocoa-500">
                        <tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Date</th></tr>
                    </thead>
                    <tbody class="divide-y divide-cocoa-900/8">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-blush-600 hover:underline">#{{ $order->order_number }}</a></td>
                                <td class="px-4 py-3 capitalize">{{ $order->status->value }}</td>
                                <td class="px-4 py-3 font-bold">${{ number_format($order->total, 2) }}</td>
                                <td class="px-4 py-3 text-cocoa-500">{{ $order->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-cocoa-500">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <h2 class="mb-3 mt-6 text-xs font-bold uppercase tracking-wide text-cocoa-500">Addresses</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @forelse ($addresses as $address)
                    <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-4 text-sm">
                        <p class="font-bold text-cocoa-900">{{ $address->first_name }} {{ $address->last_name }} <span class="text-xs font-normal capitalize text-cocoa-500">({{ $address->type->value }})</span></p>
                        <p class="text-cocoa-700">{{ $address->address_line1 }}, {{ $address->city }}, {{ $address->state }} {{ $address->zip }}</p>
                    </div>
                @empty
                    <p class="text-sm text-cocoa-500">No saved addresses.</p>
                @endforelse
            </div>
        </div>

        <div>
            <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wide text-cocoa-500">Account</h2>
                <p class="text-sm text-cocoa-700">{{ $customer->email }}</p>
                <p class="text-sm text-cocoa-700">{{ $customer->phone ?? 'No phone on file' }}</p>
                <p class="mt-3">
                    <x-badge :tone="$customer->status->value === 'active' ? 'success' : 'danger'">{{ $customer->status->value }}</x-badge>
                </p>

                <form method="POST" action="{{ route('admin.customers.status', $customer) }}" class="mt-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="{{ $customer->status->value === 'active' ? 'disabled' : 'active' }}">
                    <x-btn type="submit" :variant="$customer->status->value === 'active' ? 'danger' : 'primary'" class="w-full justify-center">
                        {{ $customer->status->value === 'active' ? 'Disable Account' : 'Enable Account' }}
                    </x-btn>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
