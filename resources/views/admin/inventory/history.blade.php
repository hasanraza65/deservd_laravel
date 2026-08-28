<x-admin-layout title="Inventory History">
    <a href="{{ route('admin.inventory.index') }}" class="mb-4 inline-block text-sm text-cocoa-600 hover:text-blush-600">&larr; Back to inventory</a>
    <h2 class="mb-4 font-display text-lg font-extrabold text-cocoa-900">{{ $product->name }} <span class="text-sm font-normal text-cocoa-500">({{ $product->sku }})</span></h2>

    <div class="overflow-x-auto rounded-lg border border-cocoa-900/10 bg-cream-50">
        <table class="w-full text-sm">
            <thead class="border-b border-cocoa-900/10 text-left text-xs font-bold uppercase tracking-wide text-cocoa-500">
                <tr><th class="px-4 py-3">Type</th><th class="px-4 py-3">Change</th><th class="px-4 py-3">Balance After</th><th class="px-4 py-3">Note</th><th class="px-4 py-3">By</th><th class="px-4 py-3">Date</th></tr>
            </thead>
            <tbody class="divide-y divide-cocoa-900/8">
                @foreach ($history as $entry)
                    <tr>
                        <td class="px-4 py-3 capitalize text-cocoa-700">{{ $entry->type }}</td>
                        <td class="px-4 py-3 font-bold {{ $entry->quantity_change < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $entry->quantity_change > 0 ? '+' : '' }}{{ $entry->quantity_change }}</td>
                        <td class="px-4 py-3">{{ $entry->quantity_after }}</td>
                        <td class="px-4 py-3 text-cocoa-600">{{ $entry->note }}</td>
                        <td class="px-4 py-3 text-cocoa-500">{{ $entry->causedBy->name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-cocoa-500">{{ $entry->created_at->format('M j, Y g:ia') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $history->links() }}</div>
</x-admin-layout>
