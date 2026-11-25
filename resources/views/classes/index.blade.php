@extends('layouts.app')
@section('title', 'Classes')
@section('content')
@php $admin = auth()->user()->isAdmin(); @endphp
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><h1 class="page-title">Classes</h1><p class="mt-1 text-sm text-slate-500">{{ $classes->count() }} classes this year</p></div>
    @if ($admin)<a href="{{ route('classes.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> New class</a>@endif
</div>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($classes as $c)
        <a href="{{ route('classes.show', $c) }}" class="card card-pad fade-up group block transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/70">
            <div class="flex items-start justify-between">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-fuchsia-500 text-white shadow-lg shadow-brand-600/25"><x-icon name="building" /></span>
                <x-badge :status="$c->status" />
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 group-hover:text-brand-700">{{ $c->name }}</h3>
            <p class="font-mono text-xs text-slate-500">{{ $c->class_code }} · {{ $c->academic_year }}</p>
            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
                <span class="flex items-center gap-2 text-slate-600">@if($c->teacher)<x-avatar :name="$c->teacher->full_name" size="h-6 w-6 text-[10px]" /> {{ $c->teacher->full_name }}@else<span class="text-slate-400">No teacher</span>@endif</span>
                <span class="font-semibold text-slate-900">{{ $c->students_count }} <span class="font-normal text-slate-500">students</span></span>
            </div>
        </a>
    @empty
        <div class="card sm:col-span-2 xl:col-span-3"><x-empty icon="building" title="No classes yet" text="Create a class, then enroll students into it.">@if($admin)<a href="{{ route('classes.create') }}" class="btn-primary btn-sm">Create a class</a>@endif</x-empty></div>
    @endforelse
</div>
@endsection
