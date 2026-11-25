@extends('layouts.app')
@section('title', 'Fees & M-Pesa')
@section('content')
@php $kes = fn ($n) => 'KES '.number_format($n); @endphp
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div><h1 class="page-title">Fees & M-Pesa</h1><p class="mt-1 text-sm text-slate-500">Collect school fees straight from a parent's phone.</p></div>
    <a href="{{ route('payments.create') }}" class="btn-green"><x-icon name="phone" class="h-4 w-4" /> Collect payment</a>
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat label="Total collected" :value="$kes($totals['collected'])" icon="banknotes" tone="emerald" />
    <x-stat label="This month" :value="$kes($totals['month'])" icon="calendar" tone="brand" />
    <x-stat label="Awaiting PIN" :value="$totals['pending']" icon="phone" tone="amber" />
    <x-stat label="Failed / cancelled" :value="$totals['failed']" icon="x-circle" tone="rose" />
</div>

<form method="GET" class="card card-pad my-6 grid gap-3 sm:grid-cols-3">
    <div class="relative sm:col-span-2"><x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400" /><input type="search" name="q" value="{{ request('q') }}" placeholder="Search receipt, phone or student" class="input pl-10"></div>
    <select name="status" class="input" onchange="this.form.submit()"><option value="">Any status</option>@foreach (['completed', 'pending', 'failed', 'cancelled'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
</form>

<div class="card overflow-hidden fade-up">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50"><tr><th class="th">Student</th><th class="th">Receipt</th><th class="th">Phone</th><th class="th">Amount</th><th class="th">Status</th><th class="th">Date</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($payments as $p)
                <tr class="transition hover:bg-slate-50/60">
                    <td class="td"><a href="{{ route('payments.show', $p) }}" class="flex items-center gap-3 font-semibold text-slate-900"><x-avatar :name="$p->student->full_name" size="h-8 w-8 text-xs" />{{ $p->student->full_name }}</a></td>
                    <td class="td font-mono text-xs">{{ $p->mpesa_receipt ?? '—' }}</td>
                    <td class="td font-mono text-xs">{{ $p->phone }}</td>
                    <td class="td font-semibold">{{ $kes($p->amount) }}</td>
                    <td class="td"><x-badge :status="$p->status" /></td>
                    <td class="td text-slate-500">{{ $p->created_at->format('j M, g:i a') }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty icon="banknotes" title="No payments found" text="Payments you collect via M-Pesa will appear here."><a href="{{ route('payments.create') }}" class="btn-green btn-sm">Collect payment</a></x-empty></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($payments->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $payments->links() }}</div>@endif
</div>
@endsection
