@extends('layouts.app')
@section('title', 'Edit teacher')
@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('teachers.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> Teachers</a>
    <h1 class="page-title mb-6">Edit {{ $teacher->full_name }}</h1>
    <form method="POST" action="{{ route('teachers.update', $teacher) }}" class="card card-pad fade-up">
        @csrf @method('PUT')
        @include('teachers._form')
        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6"><a href="{{ route('teachers.index') }}" class="btn-secondary">Cancel</a><button class="btn-primary">Save changes</button></div>
    </form>
    @if ($teacher->schoolClasses->isNotEmpty())
        <div class="card card-pad mt-6">
            <h2 class="text-base font-semibold text-slate-900">Classes taught</h2>
            <div class="mt-3 flex flex-wrap gap-2">@foreach ($teacher->schoolClasses as $c)<a href="{{ route('classes.show', $c) }}" class="rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-700 hover:bg-brand-100">{{ $c->name }}</a>@endforeach</div>
        </div>
    @endif
</div>
@endsection
