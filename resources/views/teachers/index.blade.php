@extends('layouts.app')
@section('title', 'Teachers')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><h1 class="page-title">Teachers</h1><p class="mt-1 text-sm text-slate-500">{{ $teachers->total() }} on staff</p></div>
    <a href="{{ route('teachers.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> Add teacher</a>
</div>

<form method="GET" class="card card-pad mb-6">
    <div class="relative"><x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400" /><input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or employee number" class="input pl-10"></div>
</form>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($teachers as $t)
        <div class="card card-pad fade-up flex flex-col">
            <div class="flex items-start gap-4">
                <x-avatar :name="$t->full_name" size="h-12 w-12 text-base" />
                <div class="min-w-0 flex-1">
                    <h3 class="truncate font-semibold text-slate-900">{{ $t->full_name }}</h3>
                    <p class="truncate text-sm text-slate-500">{{ $t->specialization ?: 'No specialization' }}</p>
                </div>
                <x-badge :status="$t->status" />
            </div>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Employee no.</dt><dd class="font-mono text-xs font-medium">{{ $t->employee_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd class="truncate pl-4 font-medium">{{ $t->user?->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Phone</dt><dd class="font-medium">{{ $t->phone ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Classes</dt><dd class="font-medium">{{ $t->school_classes_count }}</dd></div>
            </dl>
            <div class="mt-5 flex gap-2 border-t border-slate-100 pt-4">
                <a href="{{ route('teachers.edit', $t) }}" class="btn-secondary btn-sm flex-1"><x-icon name="pencil" class="h-4 w-4" /> Edit</a>
                <form method="POST" action="{{ route('teachers.destroy', $t) }}" data-confirm="Remove {{ $t->full_name }}? Their login will be deleted.">@csrf @method('DELETE')<button class="btn-danger btn-sm" aria-label="Delete"><x-icon name="trash" class="h-4 w-4" /></button></form>
            </div>
        </div>
    @empty
        <div class="card sm:col-span-2 xl:col-span-3"><x-empty icon="academic" title="No teachers found"><a href="{{ route('teachers.create') }}" class="btn-primary btn-sm">Add a teacher</a></x-empty></div>
    @endforelse
</div>
@if ($teachers->hasPages())<div class="mt-6">{{ $teachers->links() }}</div>@endif
@endsection
