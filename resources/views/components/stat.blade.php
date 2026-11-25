@props(['label', 'value', 'icon', 'tone' => 'brand', 'sub' => null])
@php
    $tones = [
        'brand' => 'from-brand-500 to-brand-700 shadow-brand-600/30',
        'emerald' => 'from-emerald-500 to-teal-600 shadow-emerald-600/30',
        'amber' => 'from-amber-400 to-orange-500 shadow-amber-600/30',
        'sky' => 'from-sky-500 to-blue-600 shadow-sky-600/30',
        'rose' => 'from-rose-500 to-pink-600 shadow-rose-600/30',
    ];
@endphp
<div class="card card-pad fade-up flex items-start justify-between gap-4">
    <div class="min-w-0">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <p class="mt-2 truncate text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $value }}</p>
        @if ($sub)<p class="mt-1 text-xs text-slate-500">{{ $sub }}</p>@endif
    </div>
    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-lg {{ $tones[$tone] }}">
        <x-icon :name="$icon" />
    </span>
</div>
