@extends('layouts.app')
@section('title', 'New class')
@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('classes.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> Classes</a>
    <h1 class="page-title mb-6">Create a class</h1>
    <form method="POST" action="{{ route('classes.store') }}" class="card card-pad fade-up">
        @csrf
        @include('classes._form')
        <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6"><a href="{{ route('classes.index') }}" class="btn-secondary">Cancel</a><button class="btn-primary">Create class</button></div>
    </form>
</div>
@endsection
