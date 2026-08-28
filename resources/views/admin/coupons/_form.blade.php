@php($coupon ??= null)
<div class="max-w-lg rounded-lg border border-cocoa-900/10 bg-cream-50 p-5">
    <div class="flex flex-col gap-4">
        <x-field label="Code" name="code" required :value="$coupon?->code" hint="Stored uppercase automatically" />
        <x-select label="Type" name="type" required :options="['fixed' => 'Fixed Amount', 'percentage' => 'Percentage']" :value="$coupon?->type?->value ?? 'percentage'" />
        <x-field label="Value" name="value" type="number" step="0.01" required :value="$coupon?->value" hint="Dollars for fixed, whole percent (1-100) for percentage" />
        <x-field label="Minimum Order Amount ($)" name="min_order_amount" type="number" step="0.01" :value="$coupon?->min_order_amount" />
        <x-field label="Max Discount ($, caps a percentage coupon)" name="max_discount" type="number" step="0.01" :value="$coupon?->max_discount" />
        <div class="grid grid-cols-2 gap-4">
            <x-field label="Starts At" name="starts_at" type="datetime-local" :value="$coupon?->starts_at?->format('Y-m-d\TH:i')" />
            <x-field label="Expires At" name="expires_at" type="datetime-local" :value="$coupon?->expires_at?->format('Y-m-d\TH:i')" />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <x-field label="Total Usage Limit" name="usage_limit" type="number" :value="$coupon?->usage_limit" />
            <x-field label="Per-Customer Limit" name="per_customer_limit" type="number" :value="$coupon?->per_customer_limit" />
        </div>
        <x-select label="Status" name="status" required :options="['active' => 'Active', 'inactive' => 'Inactive']" :value="$coupon?->status?->value ?? 'active'" />
        <x-btn type="submit">{{ $coupon ? 'Save Changes' : 'Create Coupon' }}</x-btn>
    </div>
</div>
