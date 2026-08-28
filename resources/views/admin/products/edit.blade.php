<x-admin-layout :title="$product->name">
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('admin.products.index') }}" class="text-sm text-cocoa-600 hover:text-blush-600">&larr; Back to products</a>
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product permanently?')">
            @csrf @method('DELETE')
            <x-btn type="submit" variant="danger">Delete Product</x-btn>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.products._form', ['product' => $product])
    </form>

    <div class="mt-6 rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
        <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Recent Inventory History</h2>
        @forelse ($product->inventoryHistories as $entry)
            <div class="flex items-center justify-between border-b border-cocoa-900/8 py-2 text-sm last:border-0">
                <span class="capitalize text-cocoa-700">{{ $entry->type }}</span>
                <span class="{{ $entry->quantity_change < 0 ? 'text-red-600' : 'text-green-600' }} font-bold">
                    {{ $entry->quantity_change > 0 ? '+' : '' }}{{ $entry->quantity_change }}
                </span>
                <span class="text-cocoa-500">now {{ $entry->quantity_after }}</span>
                <span class="text-xs text-cocoa-400">{{ $entry->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <p class="text-sm text-cocoa-500">No inventory changes recorded yet.</p>
        @endforelse

        <form method="POST" action="{{ route('admin.products.adjust-stock', $product) }}" class="mt-4 flex items-end gap-3">
            @csrf
            <x-field label="Set stock to" name="quantity" type="number" required :value="$product->stock_quantity" class="w-32" />
            <x-field label="Note (optional)" name="note" class="flex-1" />
            <x-btn type="submit" variant="outline">Adjust</x-btn>
        </form>
    </div>
</x-admin-layout>
