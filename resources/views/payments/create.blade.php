@extends('layouts.app')
@section('title', 'Collect payment')
@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('payments.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> Fees & M-Pesa</a>
    <h1 class="page-title mb-2">Collect a fee payment</h1>
    <p class="mb-6 text-sm text-slate-500">We'll send an M-Pesa prompt to the payer's phone. They just enter their PIN.</p>

    @if ($simulate)
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <x-icon name="exclamation" class="mt-0.5 h-5 w-5 shrink-0" />
            <p><strong>Demo mode is on.</strong> No real money moves — payments auto-complete after a few seconds. Set <code class="rounded bg-amber-100 px-1">MPESA_SIMULATE=false</code> to go live.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('payments.store') }}" class="card card-pad fade-up space-y-5">
        @csrf
        <x-field label="Student" name="student_id" type="select" required>
            <option value="">Select a student…</option>
            @foreach ($students as $s)
                <option value="{{ $s->id }}" @selected((int) old('student_id', $selected?->id) === $s->id)>{{ $s->full_name }} — {{ $s->admission_number }}</option>
            @endforeach
        </x-field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-field label="Amount (KES)" name="amount" type="number" min="1" max="250000" step="1" :value="$selected && $selected->balance() > 0 ? $selected->balance() : null" placeholder="5000" required />
            <x-field label="M-Pesa phone number" name="phone" type="tel" inputmode="tel" placeholder="0712 345 678" hint="Safaricom number that will receive the prompt." required />
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
            <a href="{{ route('payments.index') }}" class="btn-secondary">Cancel</a>
            <button class="btn-green"><x-icon name="phone" class="h-4 w-4" /> Send M-Pesa prompt</button>
        </div>
    </form>
</div>
@endsection
