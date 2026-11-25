@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php $user = auth()->user(); $kes = fn ($n) => 'KES '.number_format($n); @endphp

<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-sm font-medium text-brand-600">{{ now()->format('l, j F Y') }}</p>
        <h1 class="page-title mt-1">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ \Illuminate\Support\Str::before($user->name, ' ') }} 👋</h1>
        <p class="mt-1 text-sm text-slate-500">Here's what's happening at your school today.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('attendance.index') }}" class="btn-secondary"><x-icon name="clipboard" class="h-4 w-4" /> Take attendance</a>
        @if ($user->isAdmin())
            <a href="{{ route('students.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> New student</a>
        @endif
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-stat label="Active students" :value="number_format($stats['students'])" icon="users" tone="brand" />
    <x-stat label="Teachers" :value="number_format($stats['teachers'])" icon="academic" tone="sky" />
    <x-stat label="Active classes" :value="number_format($stats['classes'])" icon="building" tone="amber" />
    <x-stat label="Attendance today" :value="$stats['attendance_rate'] === null ? '—' : $stats['attendance_rate'].'%'" icon="chart" tone="emerald" :sub="$stats['attendance_rate'] === null ? 'Not marked yet' : 'Present or late'" />
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <section class="card card-pad lg:col-span-2">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Attendance — last 7 days</h2>
                <p class="text-sm text-slate-500">Percentage of students present or late</p>
            </div>
        </div>
        <div class="mt-8 flex h-52 items-end gap-3 sm:gap-5">
            @foreach ($trend as $day)
                <div class="group flex h-full flex-1 flex-col items-center justify-end gap-2">
                    <span class="text-xs font-semibold text-slate-500 opacity-0 transition group-hover:opacity-100">{{ $day['total'] ? $day['rate'].'%' : '—' }}</span>
                    <div class="flex w-full flex-1 items-end rounded-xl bg-slate-100">
                        <div class="w-full rounded-xl bg-gradient-to-t from-brand-600 to-brand-400 transition-all" style="height: {{ max($day['rate'], $day['total'] ? 4 : 0) }}%"></div>
                    </div>
                    <span class="text-xs font-medium text-slate-500">{{ $day['label'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card card-pad">
        <h2 class="text-base font-semibold text-slate-900">Biggest classes</h2>
        <div class="mt-5 space-y-4">
            @forelse ($classes as $class)
                <a href="{{ route('classes.show', $class) }}" class="block">
                    <div class="mb-1.5 flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-800">{{ $class->name }}</span>
                        <span class="text-slate-500">{{ $class->students_count }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-gradient-to-r from-brand-500 to-fuchsia-500" style="width: {{ $classes->max('students_count') ? ($class->students_count / $classes->max('students_count')) * 100 : 0 }}%"></div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-500">No classes yet.</p>
            @endforelse
        </div>
    </section>
</div>

@if ($finance)
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Fees collected" :value="$kes($finance['collected'])" icon="banknotes" tone="emerald" :sub="'Of '.$kes($finance['billed']).' billed'" />
        <x-stat label="Collected this month" :value="$kes($finance['this_month'])" icon="calendar" tone="brand" />
        <x-stat label="Outstanding" :value="$kes($finance['outstanding'])" icon="exclamation" tone="rose" />
        <x-stat label="Awaiting M-Pesa PIN" :value="$finance['pending']" icon="phone" tone="amber" />
    </div>

    <section class="card mt-6 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 sm:px-6">
            <h2 class="text-base font-semibold text-slate-900">Recent payments</h2>
            <a href="{{ route('payments.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50"><tr><th class="th">Student</th><th class="th">Receipt</th><th class="th">Amount</th><th class="th">Status</th><th class="th">When</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($finance['recent'] as $p)
                    <tr class="hover:bg-slate-50/60">
                        <td class="td"><a href="{{ route('payments.show', $p) }}" class="flex items-center gap-3 font-medium text-slate-900"><x-avatar :name="$p->student->full_name" size="h-8 w-8 text-xs" /> {{ $p->student->full_name }}</a></td>
                        <td class="td font-mono text-xs">{{ $p->mpesa_receipt ?? '—' }}</td>
                        <td class="td font-semibold">{{ $kes($p->amount) }}</td>
                        <td class="td"><x-badge :status="$p->status" /></td>
                        <td class="td text-slate-500">{{ $p->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty icon="banknotes" title="No payments yet" text="Collect your first fee payment via M-Pesa."><a href="{{ route('payments.create') }}" class="btn-primary btn-sm">Collect payment</a></x-empty></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection
