@extends('layouts.app')
@section('title', 'Students')
@section('content')
@php $admin = auth()->user()->isAdmin(); @endphp
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="page-title">Students</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $students->total() }} {{ \Illuminate\Support\Str::plural('student', $students->total()) }} found</p>
    </div>
    @if ($admin)<a href="{{ route('students.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> New student</a>@endif
</div>

<form method="GET" class="card card-pad mb-6 grid gap-3 sm:grid-cols-4">
    <div class="relative sm:col-span-2">
        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or admission number" class="input pl-10">
    </div>
    <select name="class" class="input" onchange="this.form.submit()">
        <option value="">All classes</option>
        @foreach ($classes as $c)<option value="{{ $c->id }}" @selected(request('class') == $c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <select name="status" class="input" onchange="this.form.submit()">
        <option value="">Any status</option>
        @foreach (['active', 'inactive', 'graduated'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
    </select>
</form>

<div class="card overflow-hidden fade-up">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50"><tr><th class="th">Student</th><th class="th">Admission</th><th class="th">Class</th><th class="th">Status</th><th class="th text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($students as $s)
                <tr class="transition hover:bg-slate-50/60">
                    <td class="td">
                        <a href="{{ route('students.show', $s) }}" class="flex items-center gap-3">
                            <x-avatar :name="$s->full_name" />
                            <span><span class="block font-semibold text-slate-900">{{ $s->full_name }}</span><span class="block text-xs capitalize text-slate-500">{{ $s->gender }}</span></span>
                        </a>
                    </td>
                    <td class="td font-mono text-xs">{{ $s->admission_number }}</td>
                    <td class="td">{{ $s->schoolClass?->name ?? '—' }}</td>
                    <td class="td"><x-badge :status="$s->status" /></td>
                    <td class="td">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('students.show', $s) }}" class="btn-secondary btn-sm">View</a>
                            @if ($admin)
                                <a href="{{ route('students.edit', $s) }}" class="btn-secondary btn-sm" aria-label="Edit"><x-icon name="pencil" class="h-4 w-4" /></a>
                                <form method="POST" action="{{ route('students.destroy', $s) }}" data-confirm="Delete {{ $s->full_name }}? This also removes their attendance and payments.">
                                    @csrf @method('DELETE')
                                    <button class="btn-danger btn-sm" aria-label="Delete"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty icon="users" title="No students found" text="Try a different search or filter.">@if ($admin)<a href="{{ route('students.create') }}" class="btn-primary btn-sm">Enroll a student</a>@endif</x-empty></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($students->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $students->links() }}</div>@endif
</div>
@endsection
