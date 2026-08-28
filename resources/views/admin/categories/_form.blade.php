@php($category ??= null)
<div class="max-w-lg rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
    <div class="flex flex-col gap-4">
        <x-field label="Name" name="name" required :value="$category?->name" />
        <x-field label="Slug" name="slug" :value="$category?->slug" hint="Leave blank to auto-generate" />
        <x-textarea label="Description" name="description" :value="$category?->description" />
        <x-select label="Status" name="status" required :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="$category?->status?->value ?? 'active'" />
        <x-field label="Sort Order" name="sort_order" type="number" :value="$category?->sort_order ?? 0" />
        <x-btn type="submit">{{ $category ? 'Save Changes' : 'Create Category' }}</x-btn>
    </div>
</div>
