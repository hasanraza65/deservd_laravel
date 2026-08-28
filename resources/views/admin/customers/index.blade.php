<x-admin-layout title="Customers">
    <form method="GET" class="mb-5 flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or email…"
               class="w-72 rounded-md border border-cocoa-900/20 px-3 py-2 text-sm outline-none focus:border-cocoa-900">
        <select name="status" onchange="this.form.submit()" class="rounded-md border border-cocoa-900/20 px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
        </select>
        <x-btn type="submit" variant="outline">Filter</x-btn>
    </form>

    <div class="overflow-x-auto rounded-lg border border-cocoa-900/10 bg-cream-50">
        <table class="w-full text-sm">
            <thead class="border-b border-cocoa-900/10 text-left text-xs font-bold uppercase tracking-wide text-cocoa-500">
                <tr>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Orders</th>
                    <th class="px-4 py-3">Total Spent</th>
                    <th class="px-4 py-3">Registered</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cocoa-900/8">
                @foreach ($customers as $customer)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-bold text-cocoa-900">{{ $customer->name }}</p>
                            <p class="text-xs text-cocoa-500">{{ $customer->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $customer->orders_count }}</td>
                        <td class="px-4 py-3 font-bold">${{ number_format(($customer->orders_sum_total ?? 0) / 100, 2) }}</td>
                        <td class="px-4 py-3 text-cocoa-500">{{ $customer->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3">
                            <x-badge :tone="$customer->status->value === 'active' ? 'success' : 'danger'">{{ $customer->status->value }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="text-xs font-bold text-blush-600 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
</x-admin-layout>
