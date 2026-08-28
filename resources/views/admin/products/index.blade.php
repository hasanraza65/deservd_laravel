<x-admin-layout title="Products">

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or SKU…"
                   class="w-64 rounded-md border border-cocoa-900/20 px-3 py-2 text-sm outline-none focus:border-cocoa-900">
            <select name="status" onchange="this.form.submit()" class="rounded-md border border-cocoa-900/20 px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                @foreach (['draft', 'active', 'archived'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <x-btn type="submit" variant="outline">Filter</x-btn>
        </form>
        <x-btn as="a" :href="route('admin.products.create')">+ New Product</x-btn>
    </div>

    <div class="overflow-x-auto rounded-lg border border-cocoa-900/10 bg-cream-50">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="border-b border-cocoa-900/10 text-left text-xs font-bold uppercase tracking-wide text-cocoa-500">
                <tr>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Stock</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cocoa-900/8">
                @foreach ($products as $product)
                    <tr>
                        <td class="flex items-center gap-3 px-4 py-3">
                            @if ($product->images->first())
                                <img src="{{ $product->images->first()->url }}" alt="" class="h-10 w-10 rounded object-cover">
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded bg-cocoa-100 text-[10px] text-cocoa-400">No img</span>
                            @endif
                            <div>
                                <p class="font-bold text-cocoa-900">{{ $product->name }}</p>
                                <p class="text-xs text-cocoa-500">{{ $product->category->name ?? 'Uncategorised' }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-cocoa-600">{{ $product->sku }}</td>
                        <td class="px-4 py-3 font-bold text-cocoa-900">${{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-3">
                            @if ($product->isLowStock())
                                <x-badge tone="warning">{{ $product->stock_quantity }} low</x-badge>
                            @elseif (!$product->isInStock())
                                <x-badge tone="danger">Out of stock</x-badge>
                            @else
                                {{ $product->stock_quantity }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :tone="$product->status->value === 'active' ? 'success' : 'neutral'">{{ $product->status->value }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-xs font-bold text-blush-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</x-admin-layout>
