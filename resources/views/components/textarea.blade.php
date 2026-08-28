@props(['label', 'name', 'value' => null, 'rows' => 4])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-cocoa-700">{{ $label }}</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->except('class') }}
        class="w-full resize-none rounded-md border px-3 py-2 text-sm text-cocoa-900 outline-none focus:border-cocoa-900 {{ $errors->has($name) ? 'border-red-400' : 'border-cocoa-900/20' }}"
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
