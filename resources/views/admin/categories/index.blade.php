<x-admin-layout title="Categories">
    <div class="mb-5 flex justify-end">
        <x-btn as="a" :href="route('admin.categories.create')">+ New Category</x-btn>
    </div>

    <div class="overflow-x-auto rounded-lg border border-cocoa-900/10 bg-cream-50">
        <table class="w-full text-sm">
            <thead class="border-b border-cocoa-900/10 text-left text-xs font-bold uppercase tracking-wide text-cocoa-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Products</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-cocoa-900/8">
                @foreach ($categories as $category)
                    <tr>
                        <td class="px-4 py-3 font-bold text-cocoa-900">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-cocoa-600">{{ $category->slug }}</td>
                        <td class="px-4 py-3">{{ $category->products_count }}</td>
                        <td class="px-4 py-3">
                            <x-badge :tone="$category->status->value === 'active' ? 'success' : 'neutral'">{{ $category->status->value }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="mr-3 text-xs font-bold text-blush-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-bold text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
