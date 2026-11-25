@props(['label', 'name', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="label">{{ $label }}@if($required)<span class="text-rose-500"> *</span>@endif</label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" @class(['input', 'input-error' => $errors->has($name)]) {{ $attributes->except('class') }}>{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}" @class(['input', 'input-error' => $errors->has($name)]) {{ $attributes->except('class') }}>{{ $slot }}</select>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($name, $value) }}" @class(['input', 'input-error' => $errors->has($name)]) {{ $attributes->except('class') }}>
    @endif
    @if ($hint && ! $errors->has($name))<p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
</div>
