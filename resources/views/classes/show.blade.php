@extends('layouts.app')
@section('title', $class->name)
@section('content')
@php $admin = auth()->user()->isAdmin(); @endphp
<a href="{{ route('classes.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> Classes</a>
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <div class="flex items-center gap-3"><h1 class="page-title">{{ $class->name }}</h1><x-badge :status="$class->status" /></div>
        <p class="mt-1 text-sm text-slate-500"><span class="font-mono">{{ $class->class_code }}</span> · {{ $class->academic_year }} · Teacher: {{ $class->teacher?->full_name ?? 'unassigned' }}</p>
        @if ($class->description)<p class="mt-2 max-w-2xl text-sm text-slate-600">{{ $class->description }}</p>@endif
    </div>
    <div class="flex gap-2">
        <a href="{{ route('attendance.index', ['class' => $class->id]) }}" class="btn-secondary"><x-icon name="clipboard" class="h-4 w-4" /> Attendance</a>
        @if ($admin)
            <a href="{{ route('classes.edit', $class) }}" class="btn-secondary"><x-icon name="pencil" class="h-4 w-4" /> Edit</a>
            <form method="POST" action="{{ route('classes.destroy', $class) }}" data-confirm="Delete this class?">@csrf @method('DELETE')<button class="btn-danger"><x-icon name="trash" class="h-4 w-4" /></button></form>
        @endif
    </div>
</div>

<div class="card overflow-hidden fade-up">
    <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 class="text-base font-semibold text-slate-900">Students ({{ $class->students_count }})</h2></div>
    <ul class="divide-y divide-slate-100">
        @forelse ($students as $s)
            <li><a href="{{ route('students.show', $s) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-slate-50 sm:px-6"><x-avatar :name="$s->full_name" /><span class="flex-1"><span class="block text-sm font-semibold text-slate-900">{{ $s->full_name }}</span><span class="block font-mono text-xs text-slate-500">{{ $s->admission_number }}</span></span><x-badge :status="$s->status" /></a></li>
        @empty
            <li><x-empty icon="users" title="No students in this class yet" /></li>
        @endforelse
    </ul>
</div>
@endsection
