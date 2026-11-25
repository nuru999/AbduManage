@extends('layouts.app')
@section('title', 'New teacher')
@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('teachers.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> Teachers</a>
    <h1 class="page-title mb-6">Add a teacher</h1>
    <form method="POST" action="{{ route('teachers.store') }}" class="card card-pad fade-up">
        @csrf
        @include('teachers._form')
        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6"><a href="{{ route('teachers.index') }}" class="btn-secondary">Cancel</a><button class="btn-primary">Add teacher</button></div>
    </form>
</div>
@endsection
