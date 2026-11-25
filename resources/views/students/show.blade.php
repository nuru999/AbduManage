@extends('layouts.app')
@section('title', $student->full_name)
@section('content')
@php $admin = auth()->user()->isAdmin(); $kes = fn ($n) => 'KES '.number_format($n); $balance = $student->balance(); @endphp

<a href="{{ route('students.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800"><x-icon name="arrow-left" class="h-4 w-4" /> Students</a>

<div class="card fade-up overflow-hidden">
    <div class="h-28 bg-gradient-to-r from-brand-600 via-brand-500 to-fuchsia-500"></div>
    <div class="flex flex-wrap items-end justify-between gap-4 px-6 pb-6">
        <div class="-mt-10 flex items-end gap-4">
            <x-avatar :name="$student->full_name" size="h-20 w-20 text-2xl ring-4 ring-white" />
            <div class="pb-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $student->full_name }}</h1>
                <p class="mt-0.5 text-sm text-slate-500">{{ $student->admission_number }} · {{ $student->schoolClass?->name ?? 'No class assigned' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-badge :status="$student->status" />
            @if ($admin)
                <a href="{{ route('payments.create', ['student' => $student->id]) }}" class="btn-green btn-sm"><x-icon name="phone" class="h-4 w-4" /> Collect via M-Pesa</a>
                <a href="{{ route('students.edit', $student) }}" class="btn-secondary btn-sm"><x-icon name="pencil" class="h-4 w-4" /> Edit</a>
            @endif
        </div>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="space-y-6">
        <section class="card card-pad">
            <h2 class="text-base font-semibold text-slate-900">Profile</h2>
            <dl class="mt-4 space-y-3 text-sm">
                @foreach ([
                    'Gender' => ucfirst($student->gender),
                    'Date of birth' => $student->date_of_birth?->format('j M Y') ?? '—',
                    'Phone' => $student->phone ?: '—',
                    'Enrolled' => $student->enrollment_date->format('j M Y'),
                    'Class teacher' => $student->schoolClass?->teacher?->full_name ?? '—',
                    'Address' => $student->address ?: '—',
                ] as $k => $v)
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $k }}</dt><dd class="text-right font-medium text-slate-800">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </section>

        <section class="card card-pad">
            <h2 class="text-base font-semibold text-slate-900">Attendance</h2>
            @if ($attendance['rate'] === null)
                <p class="mt-3 text-sm text-slate-500">No attendance recorded yet.</p>
            @else
                <p class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ $attendance['rate'] }}<span class="text-xl text-slate-400">%</span></p>
                <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                    @foreach (['present', 'late', 'absent', 'excused'] as $st)
                        <div class="rounded-xl bg-slate-50 px-3 py-2"><x-badge :status="$st" /><p class="mt-1.5 text-lg font-bold text-slate-900">{{ $attendance['counts'][$st] ?? 0 }}</p></div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <div class="space-y-6 lg:col-span-2">
        @if ($admin || $fees->isNotEmpty())
        <section class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Fees</h2>
                    <p class="text-sm text-slate-500">Billed {{ $kes($student->totalFees()) }} · Paid {{ $kes($student->totalPaid()) }}</p>
                </div>
                <div class="rounded-xl px-4 py-2 text-right {{ $balance > 0 ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">
                    <p class="text-xs font-semibold uppercase tracking-wide">{{ $balance > 0 ? 'Balance due' : 'Cleared' }}</p>
                    <p class="text-lg font-bold">{{ $kes(max($balance, 0)) }}</p>
                </div>
            </div>

            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50"><tr><th class="th">Fee</th><th class="th">Due</th><th class="th text-right">Amount</th>@if($admin)<th class="th"></th>@endif</tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($fees as $fee)
                    <tr><td class="td font-medium">{{ $fee->title }}</td><td class="td text-slate-500">{{ $fee->due_date?->format('j M Y') ?? '—' }}</td><td class="td text-right font-semibold">{{ $kes($fee->amount) }}</td>
                    @if ($admin)<td class="td text-right"><form method="POST" action="{{ route('fees.destroy', $fee) }}" data-confirm="Remove this fee?">@csrf @method('DELETE')<button class="text-slate-400 hover:text-rose-600" aria-label="Remove fee"><x-icon name="trash" class="h-4 w-4" /></button></form></td>@endif</tr>
                @empty
                    <tr><td colspan="4" class="td py-8 text-center text-slate-500">No fees billed yet.</td></tr>
                @endforelse
                </tbody>
            </table>

            @if ($admin)
            <form method="POST" action="{{ route('fees.store', $student) }}" class="grid gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4 sm:grid-cols-12 sm:px-6">
                @csrf
                <input name="title" required placeholder="e.g. Term 1 Tuition" class="input sm:col-span-5" value="{{ old('title') }}">
                <input name="amount" type="number" min="1" required placeholder="Amount (KES)" class="input sm:col-span-3" value="{{ old('amount') }}">
                <input name="due_date" type="date" class="input sm:col-span-2" value="{{ old('due_date') }}">
                <button class="btn-primary sm:col-span-2">Add fee</button>
                @if ($errors->hasAny(['title', 'amount', 'due_date']))<p class="text-xs font-medium text-rose-600 sm:col-span-12">{{ $errors->first() }}</p>@endif
            </form>
            @endif
        </section>

        <section class="card overflow-hidden">
            <div class="px-5 py-4 sm:px-6"><h2 class="text-base font-semibold text-slate-900">Payment history</h2></div>
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50"><tr><th class="th">Receipt</th><th class="th">Amount</th><th class="th">Status</th><th class="th">Date</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($payments as $p)
                    <tr class="hover:bg-slate-50/60"><td class="td font-mono text-xs">@if($admin)<a href="{{ route('payments.show', $p) }}" class="text-brand-600 hover:underline">{{ $p->mpesa_receipt ?? '#'.$p->id }}</a>@else{{ $p->mpesa_receipt ?? '#'.$p->id }}@endif</td><td class="td font-semibold">{{ $kes($p->amount) }}</td><td class="td"><x-badge :status="$p->status" /></td><td class="td text-slate-500">{{ ($p->paid_at ?? $p->created_at)->format('j M Y, g:i a') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="td py-8 text-center text-slate-500">No payments yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </section>
        @endif

        <section class="card overflow-hidden">
            <div class="px-5 py-4 sm:px-6"><h2 class="text-base font-semibold text-slate-900">Recent attendance</h2></div>
            <div class="flex flex-wrap gap-2 px-5 pb-5 sm:px-6">
                @forelse ($recentAttendance as $a)
                    <div class="rounded-xl bg-slate-50 px-3 py-2 text-center"><p class="text-xs text-slate-500">{{ $a->attendance_date->format('D j M') }}</p><x-badge :status="$a->status" class="mt-1" /></div>
                @empty
                    <p class="text-sm text-slate-500">Nothing recorded yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
