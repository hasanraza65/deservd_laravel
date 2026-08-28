<x-admin-layout title="Coupons">
    <div class="mb-5 flex justify-end">
        <x-btn as="a" :href="route('admin.coupons.create')">+ New Coupon</x-btn>
    </div>

    <div class="overflow-x-auto rounded-lg border border-cocoa-900/10 bg-cream-50">
        <table class="w-full text-sm">
            <thead class="border-b border-cocoa-900/10 text-left text-xs font-bold uppercase tracking-wide text-cocoa-500">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Value</th>
                    <th class="px-4 py-3">Usage</th>
                    <th class="px-4 py-3">Expires</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cocoa-900/8">
                @foreach ($coupons as $coupon)
                    <tr>
                        <td class="px-4 py-3 font-display font-bold text-cocoa-900">{{ $coupon->code }}</td>
                        <td class="px-4 py-3 capitalize">{{ $coupon->type->value }}</td>
                        <td class="px-4 py-3">{{ $coupon->type->value === 'percentage' ? $coupon->value . '%' : '$' . number_format($coupon->value, 2) }}</td>
                        <td class="px-4 py-3">{{ $coupon->usage_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}</td>
                        <td class="px-4 py-3 text-cocoa-500">{{ $coupon->expires_at?->format('M j, Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-badge :tone="$coupon->isCurrentlyActive() ? 'success' : 'neutral'">{{ $coupon->isCurrentlyActive() ? 'Active' : 'Inactive' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="mr-3 text-xs font-bold text-blush-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Delete this coupon?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-bold text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $coupons->links() }}</div>
</x-admin-layout>
