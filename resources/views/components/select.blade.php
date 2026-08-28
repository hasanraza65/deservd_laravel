@props(['label', 'name', 'options' => [], 'value' => null, 'required' => false])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-cocoa-700">
        {{ $label }} @if($required)<span class="text-blush-500">*</span>@endif
    </label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->except('class') }}
        class="w-full rounded-md border px-3 py-2 text-sm text-cocoa-900 outline-none focus:border-cocoa-900 {{ $errors->has($name) ? 'border-red-400' : 'border-cocoa-900/20' }}"
    >
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
