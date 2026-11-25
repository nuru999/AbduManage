@extends('layouts.app')
@section('title', 'New student')
@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('students.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> Students</a>
    <h1 class="page-title mb-6">Enroll a student</h1>
    <form method="POST" action="{{ route('students.store') }}" class="card card-pad fade-up">
        @csrf
        @include('students._form')
        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('students.index') }}" class="btn-secondary">Cancel</a>
            <button class="btn-primary">Enroll student</button>
        </div>
    </form>
</div>
@endsection
