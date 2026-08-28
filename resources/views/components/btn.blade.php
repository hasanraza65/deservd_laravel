@props(['variant' => 'primary', 'as' => 'button', 'href' => null])
@php
    $variants = [
        'primary' => 'bg-blush-500 text-white hover:bg-blush-600',
        'dark' => 'bg-cocoa-900 text-cream-100 hover:bg-cocoa-800',
        'outline' => 'border border-cocoa-900/20 text-cocoa-900 hover:bg-cocoa-100',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
        'ghost' => 'text-cocoa-700 hover:bg-cocoa-100',
    ];
    $classes = 'inline-flex items-center justify-center gap-1.5 rounded-md px-4 py-2 text-xs font-bold uppercase tracking-wide transition-colors disabled:opacity-50 ' . ($variants[$variant] ?? $variants['primary']);
@endphp
@if ($as === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
