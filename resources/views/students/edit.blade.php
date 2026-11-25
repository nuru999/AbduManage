@extends('layouts.app')
@section('title', 'Edit '.$student->full_name)
@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('students.show', $student) }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> {{ $student->full_name }}</a>
    <h1 class="page-title mb-6">Edit student</h1>
    <form method="POST" action="{{ route('students.update', $student) }}" class="card card-pad fade-up">
        @csrf @method('PUT')
        @include('students._form')
        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('students.show', $student) }}" class="btn-secondary">Cancel</a>
            <button class="btn-primary">Save changes</button>
        </div>
    </form>
</div>
@endsection
