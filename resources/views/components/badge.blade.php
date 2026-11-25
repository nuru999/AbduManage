@props(['status'])
@php
    $map = [
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'late' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'inactive' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        'graduated' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'excused' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'failed' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'absent' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
    ];
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 ring-inset', $map[$status] ?? $map['inactive']]) }}>
    @if ($status === 'pending')<span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"></span>@endif
    {{ $slot->isEmpty() ? $status : $slot }}
</span>
