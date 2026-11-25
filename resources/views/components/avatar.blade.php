@props(['name', 'size' => 'h-10 w-10 text-sm'])
@php
    $initials = collect(explode(' ', trim($name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $palette = ['from-violet-500 to-indigo-600', 'from-sky-500 to-blue-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-fuchsia-500 to-purple-600'];
    $color = $palette[crc32($name) % count($palette)];
@endphp
<span {{ $attributes->class(["inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br font-semibold text-white shadow-sm", $size, $color]) }}>{{ $initials }}</span>
