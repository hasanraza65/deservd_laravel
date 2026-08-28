@props(['label', 'value', 'hint' => null])
<div {{ $attributes->merge(['class' => 'rounded-lg border border-cocoa-900/10 bg-cream-50 p-5']) }}>
    <p class="text-xs font-bold uppercase tracking-wide text-cocoa-500">{{ $label }}</p>
    <p class="mt-2 font-display text-2xl font-extrabold text-cocoa-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-cocoa-500">{{ $hint }}</p>
    @endif
</div>
