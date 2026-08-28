@props(['tone' => 'neutral'])
@php
    $tones = [
        'neutral' => 'bg-cocoa-100 text-cocoa-700',
        'success' => 'bg-green-100 text-green-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'danger' => 'bg-red-100 text-red-700',
        'pink' => 'bg-blush-100 text-blush-700',
        'dark' => 'bg-cocoa-900 text-cream-100',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide ' . ($tones[$tone] ?? $tones['neutral'])]) }}>
    {{ $slot }}
</span>
