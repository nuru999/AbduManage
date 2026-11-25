@extends('layouts.app')
@section('title', 'Attendance')
@section('content')
<div class="mb-6">
    <h1 class="page-title">Attendance</h1>
    <p class="mt-1 text-sm text-slate-500">Pick a class and date, mark each student and save.</p>
</div>

<form method="GET" class="card card-pad mb-6 grid gap-3 sm:grid-cols-3">
    <div>
        <label class="label" for="class">Class</label>
        <select id="class" name="class" class="input" onchange="this.form.submit()">
            @foreach ($classes as $c)<option value="{{ $c->id }}" @selected($class && $class->id === $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="label" for="date">Date</label>
        <input id="date" type="date" name="date" value="{{ $date }}" max="{{ today()->toDateString() }}" class="input" onchange="this.form.submit()">
    </div>
    <div class="flex items-end"><button class="btn-secondary w-full">Load class</button></div>
</form>

@if (! $class)
    <div class="card"><x-empty icon="clipboard" title="No classes available" text="You need an active class assigned to you before you can take attendance." /></div>
@elseif ($students->isEmpty())
    <div class="card"><x-empty icon="users" :title="'No active students in '.$class->name" text="Enroll students into this class first." /></div>
@else
    @php
        $options = [
            'present' => ['Present', 'peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:ring-emerald-500'],
            'late' => ['Late', 'peer-checked:bg-amber-500 peer-checked:text-white peer-checked:ring-amber-500'],
            'absent' => ['Absent', 'peer-checked:bg-rose-500 peer-checked:text-white peer-checked:ring-rose-500'],
            'excused' => ['Excused', 'peer-checked:bg-sky-500 peer-checked:text-white peer-checked:ring-sky-500'],
        ];
    @endphp
    <form method="POST" action="{{ route('attendance.store') }}" class="card overflow-hidden fade-up">
        @csrf
        <input type="hidden" name="school_class_id" value="{{ $class->id }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-base font-semibold text-slate-900">{{ $class->name }}</h2>
                <p class="text-sm text-slate-500">{{ \Illuminate\Support\Carbon::parse($date)->format('l, j F Y') }} · {{ $students->count() }} students @if ($existing->isNotEmpty())· <span class="font-medium text-emerald-600">already recorded</span>@endif</p>
            </div>
            <button type="button" data-mark-all="present" class="btn-secondary btn-sm"><x-icon name="check" class="h-4 w-4" /> Mark all present</button>
        </div>

        <ul class="divide-y divide-slate-100">
            @foreach ($students as $s)
                @php $current = old("status.{$s->id}", $existing[$s->id]->status ?? 'present'); @endphp
                <li class="grid items-center gap-3 px-5 py-4 sm:px-6 lg:grid-cols-12">
                    <div class="flex items-center gap-3 lg:col-span-4"><x-avatar :name="$s->full_name" /><div><p class="text-sm font-semibold text-slate-900">{{ $s->full_name }}</p><p class="font-mono text-xs text-slate-500">{{ $s->admission_number }}</p></div></div>
                    <div class="grid grid-cols-4 gap-2 lg:col-span-5">
                        @foreach ($options as $value => [$label, $css])
                            <label class="cursor-pointer">
                                <input type="radio" class="peer sr-only" name="status[{{ $s->id }}]" value="{{ $value }}" @checked($current === $value)>
                                <span class="block rounded-lg bg-slate-50 px-2 py-2 text-center text-xs font-semibold text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-100 sm:text-sm {{ $css }}">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="lg:col-span-3"><input type="text" name="remarks[{{ $s->id }}]" value="{{ old("remarks.{$s->id}", $existing[$s->id]->remarks ?? '') }}" placeholder="Remark (optional)" class="input py-2"></div>
                </li>
            @endforeach
        </ul>

        <div class="flex justify-end border-t border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6"><button class="btn-primary">Save attendance</button></div>
    </form>
@endif
@endsection
