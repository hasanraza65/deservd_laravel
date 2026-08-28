@php
    /** @var \App\Models\Product|null $product */
    $product ??= null;

    // Built here rather than inline on the <x-select> tag: an inline array
    // literal needs a PHP string containing an apostrophe ("DESERV'D XX"),
    // which forces escaped double-quotes inside an already double-quoted
    // Blade attribute — Blade's component-tag parser mis-reads where that
    // attribute ends when it hits the escaped quotes, and silently renders
    // the tag as literal unprocessed HTML instead of a real <select>.
    $productTypeOptions = ['standard' => 'Standard Cookie', 'deservd_xx' => "DESERV'D XX"];
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <div class="flex flex-col gap-5 lg:col-span-2">
        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Basic Information</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Name" name="name" required :value="$product?->name" class="sm:col-span-2" />
                <x-field label="Slug" name="slug" :value="$product?->slug" hint="Leave blank to auto-generate" />
                <x-field label="SKU" name="sku" required :value="$product?->sku" />
                <x-textarea label="Short Description" name="short_description" :value="$product?->short_description" rows="2" class="sm:col-span-2" />
                <x-textarea label="Description" name="description" :value="$product?->description" rows="4" class="sm:col-span-2" />
            </div>
        </div>

        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Pricing & Stock</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Price ($)" name="price" type="number" step="0.01" required :value="$product?->price" />
                <x-field label="Compare-at Price ($)" name="compare_at_price" type="number" step="0.01" :value="$product?->compare_at_price" />
                <x-field label="Stock Quantity" name="stock_quantity" type="number" required :value="$product?->stock_quantity ?? 0" />
                <x-field label="Low Stock Threshold" name="low_stock_threshold" type="number" required :value="$product?->low_stock_threshold ?? 10" />
            </div>
        </div>

        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Nutrition</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-field label="Weight" name="weight" :value="$product?->weight" placeholder="120g" />
                <x-field label="Protein (g)" name="protein_grams" type="number" :value="$product?->protein_grams" />
                <x-field label="Calories" name="calories" type="number" :value="$product?->calories" />
                <x-field label="Carbs (g)" name="carbohydrates_grams" type="number" step="0.01" :value="$product?->carbohydrates_grams" />
                <x-field label="Fat (g)" name="fat_grams" type="number" step="0.01" :value="$product?->fat_grams" />
                <x-field label="Fiber (g)" name="fiber_grams" type="number" step="0.01" :value="$product?->fiber_grams" />
                <x-field label="Sugar (g)" name="sugar_grams" type="number" step="0.01" :value="$product?->sugar_grams" />
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-field label="Ingredients (comma separated)" name="ingredients_text"
                    :value="$product ? implode(', ', $product->ingredients ?? []) : ''" />
                <x-field label="Allergens (comma separated)" name="allergens_text"
                    :value="$product ? implode(', ', $product->allergens ?? []) : ''" />
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-textarea label="Storage Info" name="storage_info" :value="$product?->storage_info" rows="2" />
                <x-textarea label="Shipping Info" name="shipping_info" :value="$product?->shipping_info" rows="2" />
            </div>
        </div>

        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Images</h2>
            @if ($product && $product->images->isNotEmpty())
                <div class="mb-4 grid grid-cols-3 gap-3 sm:grid-cols-5">
                    @foreach ($product->images as $image)
                        <div class="group relative overflow-hidden rounded-md border border-cocoa-900/10">
                            <img src="{{ $image->url }}" alt="" class="aspect-square w-full object-cover">
                            @if ($image->is_primary)
                                <span class="absolute left-1 top-1 rounded bg-blush-500 px-1.5 py-0.5 text-[9px] font-bold text-white">PRIMARY</span>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-black/50 p-1 opacity-0 group-hover:opacity-100">
                                @unless ($image->is_primary)
                                    <form method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}">
                                        @csrf @method('POST')
                                        <button class="text-[9px] font-bold text-white hover:underline">Make primary</button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" onsubmit="return confirm('Delete this image?')">
                                    @csrf @method('DELETE')
                                    <button class="text-[9px] font-bold text-red-300 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <input type="file" name="images[]" multiple accept="image/*" class="block w-full text-sm text-cocoa-700 file:mr-3 file:rounded-md file:border-0 file:bg-cocoa-900 file:px-3 file:py-2 file:text-xs file:font-bold file:uppercase file:text-cream-100">
            <p class="mt-1 text-xs text-cocoa-500">The first uploaded image becomes primary if none is set yet.</p>
        </div>
    </div>

    <div class="flex flex-col gap-5">
        <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Organisation</h2>
            <div class="flex flex-col gap-4">
                <x-select label="Category" name="category_id" :options="['' => '— None —'] + $categories->pluck('name', 'id')->all()" :value="$product?->category_id" />
                <x-select label="Product Type" name="product_type" required
                    :options="$productTypeOptions"
                    :value="$product?->product_type?->value ?? 'standard'" />
                <x-select label="Status" name="status" required
                    :options="['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived']"
                    :value="$product?->status?->value ?? 'draft'" />
                <label class="flex items-center gap-2 text-sm text-cocoa-700">
                    <input type="checkbox" name="is_featured" value="1" @checked($product?->is_featured) class="rounded border-cocoa-900/30">
                    Featured product
                </label>
            </div>
        </div>

        <x-btn type="submit" class="w-full justify-center">{{ $product ? 'Save Changes' : 'Create Product' }}</x-btn>
    </div>
</div>
