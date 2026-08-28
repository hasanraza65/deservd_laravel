@php
    use App\Enums\OrderStatus;
    $workflow = OrderStatus::workflowSteps();
    // array_search() returns false for a New order (not yet in the workflow
    // array) or a Cancelled/Refunded one — false directly as an int would
    // loosely-compare against $i via PHP's bool-coercion rules and highlight
    // the first step by accident. -1 makes "no step reached yet" explicit.
    $currentIndex = array_search($order->status, $workflow, true);
    $currentIndex = $currentIndex === false ? -1 : $currentIndex;
@endphp
<x-admin-layout title="Order #{{ $order->order_number }}">
    <a href="{{ route('admin.orders.index') }}" class="mb-4 inline-block text-sm text-cocoa-600 hover:text-blush-600">&larr; Back to orders</a>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-5 lg:col-span-2">
            {{-- Fulfilment workflow tracker --}}
            <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-display text-lg font-extrabold text-cocoa-900">Order #{{ $order->order_number }}</h2>
                    <div class="flex gap-2">
                        <x-badge :tone="$order->payment_status->value === 'paid' ? 'success' : 'warning'">{{ $order->payment_status->value }}</x-badge>
                        <x-badge tone="pink">{{ $order->status->label() }}</x-badge>
                    </div>
                </div>

                {{-- New orders must still be able to advance into the workflow —
                     only truly terminal states (cancelled/refunded) hide these buttons. --}}
                @unless (in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Refunded], true))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($workflow as $i => $step)
                            <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ $step->value }}">
                                <button
                                    type="submit"
                                    class="rounded-md border px-3 py-2 text-xs font-bold uppercase tracking-wide transition-colors
                                        {{ $i <= $currentIndex ? 'border-cocoa-900 bg-cocoa-900 text-cream-100' : 'border-cocoa-900/20 text-cocoa-700 hover:border-cocoa-900/50' }}"
                                >
                                    {{ $step->label() }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endunless

                <div class="mt-5 flex flex-wrap gap-2 border-t border-cocoa-900/10 pt-4">
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="cancelled">
                        <x-btn type="submit" variant="ghost">Cancel Order</x-btn>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="payment_status" value="refunded">
                        <x-btn type="submit" variant="ghost">Mark Refunded</x-btn>
                    </form>
                    <x-btn as="a" href="#" variant="outline" onclick="window.print(); return false;">Print Order</x-btn>
                </div>
            </div>

            {{-- Items --}}
            <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Items</h2>

                @foreach ($order->standaloneItems as $item)
                    <div class="flex items-center justify-between border-b border-cocoa-900/8 py-3 text-sm last:border-0">
                        <span class="text-cocoa-800">{{ $item->quantity }} × {{ $item->product_name }}</span>
                        <span class="font-bold text-cocoa-900">${{ number_format($item->total_price, 2) }}</span>
                    </div>
                @endforeach

                @foreach ($order->itemGroups as $group)
                    <div class="border-b border-cocoa-900/8 py-3 last:border-0">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="font-display text-sm font-extrabold uppercase text-cocoa-900">Box: {{ $group->box_size }} Pack</span>
                            <span class="font-bold text-cocoa-900">${{ number_format($group->price, 2) }}</span>
                        </div>
                        <div class="ml-4 flex flex-col gap-1">
                            @foreach ($group->items as $item)
                                <span class="text-sm text-cocoa-600">{{ $item->quantity }} × {{ $item->product_name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($order->notes)
                <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                    <h2 class="mb-2 text-xs font-bold uppercase tracking-wide text-cocoa-500">Notes</h2>
                    <p class="text-sm text-cocoa-700">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-5">
            <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wide text-cocoa-500">Customer</h2>
                <p class="font-bold text-cocoa-900">{{ $order->user->name ?? 'Guest checkout' }}</p>
                <p class="text-sm text-cocoa-600">{{ $order->customer_email }}</p>
                <p class="text-sm text-cocoa-600">{{ $order->customer_phone }}</p>
                @if ($order->user)
                    <a href="{{ route('admin.customers.show', $order->user) }}" class="mt-2 inline-block text-xs font-bold text-blush-600 hover:underline">View customer &rarr;</a>
                @endif
            </div>

            @if ($order->shipping_address)
                <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                    <h2 class="mb-3 text-xs font-bold uppercase tracking-wide text-cocoa-500">Shipping Address</h2>
                    <p class="text-sm text-cocoa-700">
                        {{ $order->shipping_address['first_name'] ?? '' }} {{ $order->shipping_address['last_name'] ?? '' }}<br>
                        {{ $order->shipping_address['address_line1'] ?? '' }}<br>
                        {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['zip'] ?? '' }}
                    </p>
                </div>
            @endif

            <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wide text-cocoa-500">Totals</h2>
                <dl class="flex flex-col gap-2 text-sm">
                    <div class="flex justify-between"><dt class="text-cocoa-600">Subtotal</dt><dd class="font-bold">${{ number_format($order->subtotal, 2) }}</dd></div>
                    @if ($order->discount_amount > 0)
                        <div class="flex justify-between text-blush-600"><dt>Discount ({{ $order->coupon_code }})</dt><dd class="font-bold">-${{ number_format($order->discount_amount, 2) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-cocoa-600">Shipping ({{ $order->shipping_method_name }})</dt><dd class="font-bold">${{ number_format($order->shipping_cost, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-cocoa-600">Tax</dt><dd class="font-bold">${{ number_format($order->tax_amount, 2) }}</dd></div>
                    <div class="flex justify-between border-t border-cocoa-900/10 pt-2 text-base"><dt class="font-bold text-cocoa-900">Total</dt><dd class="font-display font-extrabold text-cocoa-900">${{ number_format($order->total, 2) }}</dd></div>
                </dl>
                <p class="mt-3 text-xs text-cocoa-500">Payment method: {{ $order->payment_method ?? 'N/A' }}</p>
                <p class="text-xs text-cocoa-500">Placed {{ $order->created_at->format('M j, Y \a\t g:ia') }}</p>
            </div>
        </div>
    </div>
</x-admin-layout>
