@extends('layouts.app')
@section('title', 'Payment #'.$payment->id)
@section('content')
@php $kes = fn ($n) => 'KES '.number_format($n); @endphp
<div class="mx-auto max-w-xl">
    <a href="{{ route('payments.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> Fees & M-Pesa</a>

    <div class="card fade-up overflow-hidden" id="payment-card" data-status-url="{{ route('payments.status', $payment) }}" data-status="{{ $payment->status }}">
        <div id="hero" class="px-6 py-10 text-center">
            <div id="hero-icon" class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full"></div>
            <h1 id="hero-title" class="text-2xl font-bold tracking-tight text-slate-900"></h1>
            <p id="hero-text" class="mx-auto mt-2 max-w-sm text-sm text-slate-500"></p>
            <p class="mt-5 text-4xl font-bold tracking-tight text-slate-900">{{ $kes($payment->amount) }}</p>
        </div>

        <dl class="space-y-3 border-t border-slate-100 bg-slate-50/60 px-6 py-5 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Student</dt><dd class="font-semibold"><a href="{{ route('students.show', $payment->student) }}" class="hover:text-brand-700">{{ $payment->student->full_name }}</a></dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Admission no.</dt><dd class="font-mono text-xs font-medium">{{ $payment->student->admission_number }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Phone</dt><dd class="font-mono text-xs font-medium">{{ $payment->phone }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">M-Pesa receipt</dt><dd id="receipt" class="font-mono text-xs font-semibold">{{ $payment->mpesa_receipt ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Started</dt><dd class="font-medium">{{ $payment->created_at->format('j M Y, g:i a') }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Initiated by</dt><dd class="font-medium">{{ $payment->initiator?->name ?? '—' }}</dd></div>
        </dl>

        <div id="actions" class="hidden gap-3 border-t border-slate-100 px-6 py-4">
            <a href="{{ route('payments.create', ['student' => $payment->student_id]) }}" class="btn-secondary flex-1">Try again</a>
            <a href="{{ route('students.show', $payment->student) }}" class="btn-primary flex-1">Back to student</a>
        </div>
    </div>
</div>

<script>
(() => {
    const card = document.getElementById('payment-card');
    const url = card.dataset.statusUrl;
    const states = {
        pending:   { title: 'Waiting for PIN…', text: 'A prompt was sent to the phone. Ask the payer to enter their M-Pesa PIN to approve.', bg: 'bg-amber-50 text-amber-600', icon: '<svg class="h-9 w-9 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>' },
        completed: { title: 'Payment received', text: 'The fee payment has been confirmed and added to the student\'s record.', bg: 'bg-emerald-50 text-emerald-600', icon: '<svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>' },
        failed:    { title: 'Payment failed', text: 'The payment did not go through.', bg: 'bg-rose-50 text-rose-600', icon: '<svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>' },
        cancelled: { title: 'Payment cancelled', text: 'The payer cancelled the request on their phone.', bg: 'bg-rose-50 text-rose-600', icon: '<svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>' },
    };

    const render = (status, message) => {
        const s = states[status] || states.failed;
        const icon = document.getElementById('hero-icon');
        icon.className = 'mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full ' + s.bg;
        icon.innerHTML = s.icon;
        document.getElementById('hero-title').textContent = s.title;
        document.getElementById('hero-text').textContent = (status === 'failed' && message) ? message : s.text;
        document.getElementById('actions').classList.toggle('hidden', status === 'pending');
        document.getElementById('actions').classList.toggle('flex', status !== 'pending');
    };

    let status = card.dataset.status;
    render(status);
    if (status !== 'pending') return;

    let tries = 0;
    const poll = async () => {
        tries++;
        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (data.receipt) document.getElementById('receipt').textContent = data.receipt;
            if (data.status !== 'pending') { render(data.status, data.message); return; }
        } catch (e) { /* network blip: keep polling */ }
        if (tries < 60) setTimeout(poll, 3000);
        else document.getElementById('hero-text').textContent = 'Still waiting. You can leave this page — the payment will update automatically when M-Pesa responds.';
    };
    setTimeout(poll, 2500);
})();
</script>
@endsection
