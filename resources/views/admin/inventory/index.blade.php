<x-admin-layout title="Inventory">
    <div class="mb-5 flex gap-2">
        <a href="{{ route('admin.inventory.index') }}" class="rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wide {{ !request('low_stock_only') && !request('out_of_stock_only') ? 'border-cocoa-900 bg-cocoa-900 text-cream-100' : 'border-cocoa-900/20 text-cocoa-700' }}">All</a>
        <a href="{{ route('admin.inventory.index', ['low_stock_only' => 1]) }}" class="rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wide {{ request('low_stock_only') ? 'border-cocoa-900 bg-cocoa-900 text-cream-100' : 'border-cocoa-900/20 text-cocoa-700' }}">Low Stock</a>
        <a href="{{ route('admin.inventory.index', ['out_of_stock_only' => 1]) }}" class="rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wide {{ request('out_of_stock_only') ? 'border-cocoa-900 bg-cocoa-900 text-cream-100' : 'border-cocoa-900/20 text-cocoa-700' }}">Out of Stock</a>
    </div>

    <div class="overflow-x-auto rounded-lg border border-cocoa-900/10 bg-cream-50">
        <table class="w-full text-sm">
            <thead class="border-b border-cocoa-900/10 text-left text-xs font-bold uppercase tracking-wide text-cocoa-500">
                <tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3">Low Threshold</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-cocoa-900/8">
                @foreach ($products as $product)
                    <tr>
                        <td class="px-4 py-3 font-bold text-cocoa-900">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-cocoa-600">{{ $product->sku }}</td>
                        <td class="px-4 py-3 font-bold">{{ $product->stock_quantity }}</td>
                        <td class="px-4 py-3 text-cocoa-500">{{ $product->low_stock_threshold }}</td>
                        <td class="px-4 py-3">
                            @if ($product->stock_quantity <= 0)
                                <x-badge tone="danger">Out of stock</x-badge>
                            @elseif ($product->stock_quantity <= $product->low_stock_threshold)
                                <x-badge tone="warning">Low stock</x-badge>
                            @else
                                <x-badge tone="success">In stock</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="mr-3 text-xs font-bold text-blush-600 hover:underline">Adjust</a>
                            <a href="{{ route('admin.inventory.history', $product) }}" class="text-xs font-bold text-cocoa-600 hover:underline">History</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</x-admin-layout>
