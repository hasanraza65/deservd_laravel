<x-admin-layout title="Settings">
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5 lg:col-span-2">
            @csrf @method('PUT')
            <h2 class="mb-4 text-xs font-bold uppercase tracking-wide text-cocoa-500">Store Settings</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Store Name" name="store_name" :value="$settings['store_name'] ?? ''" />
                <x-field label="Store Email" name="store_email" type="email" :value="$settings['store_email'] ?? ''" />
                <x-field label="Primary Phone" name="store_phone_primary" :value="$settings['store_phone_primary'] ?? ''" />
                <x-field label="Secondary Phone" name="store_phone_secondary" :value="$settings['store_phone_secondary'] ?? ''" />
                <x-field label="Currency" name="currency" :value="$settings['currency'] ?? 'USD'" />
                <x-field label="Tax Rate (%)" name="tax_rate_percent" type="number" step="1" :value="$settings['tax_rate_percent'] ?? 0" />
                <x-field label="Free Shipping Threshold (cents)" name="free_shipping_threshold_cents" type="number" :value="$settings['free_shipping_threshold_cents'] ?? 4500" hint="4500 = $45.00" />
            </div>
            <div class="mt-4 flex gap-6">
                <label class="flex items-center gap-2 text-sm text-cocoa-700">
                    <input type="checkbox" name="local_pickup_enabled" value="1" @checked($settings['local_pickup_enabled'] ?? false) class="rounded border-cocoa-900/30">
                    Local pickup available
                </label>
                <label class="flex items-center gap-2 text-sm text-cocoa-700">
                    <input type="checkbox" name="store_open" value="1" @checked($settings['store_open'] ?? true) class="rounded border-cocoa-900/30">
                    Store open for orders
                </label>
            </div>
            <x-btn type="submit" class="mt-5">Save Settings</x-btn>
        </form>

        <div class="flex flex-col gap-5">
            <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wide text-cocoa-500">Shipping Methods</h2>
                @foreach ($shippingMethods as $method)
                    <form method="POST" action="{{ route('admin.settings.shipping-methods.update', $method) }}" class="mb-3 flex items-end gap-2 border-b border-cocoa-900/8 pb-3 last:mb-0 last:border-0">
                        @csrf @method('PUT')
                        <div class="flex-1">
                            <p class="text-sm font-bold text-cocoa-900">{{ $method->name }}</p>
                            <input type="number" step="0.01" name="price" value="{{ $method->price }}" class="mt-1 w-24 rounded border border-cocoa-900/20 px-2 py-1 text-sm">
                        </div>
                        <label class="flex items-center gap-1 text-xs text-cocoa-600">
                            <input type="checkbox" name="is_active" value="1" @checked($method->is_active)> Active
                        </label>
                        <x-btn type="submit" variant="outline">Save</x-btn>
                    </form>
                @endforeach
            </div>

            <div class="rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-wide text-cocoa-500">Build-a-Box Pricing</h2>
                @foreach ($boxOptions as $option)
                    <form method="POST" action="{{ route('admin.settings.box-options.update', $option) }}" class="mb-3 flex items-end gap-2 border-b border-cocoa-900/8 pb-3 last:mb-0 last:border-0">
                        @csrf @method('PUT')
                        <div class="flex-1">
                            <p class="text-sm font-bold text-cocoa-900">{{ $option->size }} Cookies</p>
                            <input type="number" step="0.01" name="price" value="{{ $option->price }}" class="mt-1 w-24 rounded border border-cocoa-900/20 px-2 py-1 text-sm">
                        </div>
                        <label class="flex items-center gap-1 text-xs text-cocoa-600">
                            <input type="checkbox" name="is_active" value="1" @checked($option->is_active)> Active
                        </label>
                        <x-btn type="submit" variant="outline">Save</x-btn>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
