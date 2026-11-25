<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%236d54f7'/><text x='50' y='68' font-size='56' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='700'>A</text></svg>">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
@php
    $user = auth()->user();
    $nav = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'roles' => ['admin', 'teacher']],
        ['route' => 'students.index', 'match' => 'students.*', 'label' => 'Students', 'icon' => 'users', 'roles' => ['admin', 'teacher']],
        ['route' => 'teachers.index', 'match' => 'teachers.*', 'label' => 'Teachers', 'icon' => 'academic', 'roles' => ['admin']],
        ['route' => 'classes.index', 'match' => 'classes.*', 'label' => 'Classes', 'icon' => 'building', 'roles' => ['admin', 'teacher']],
        ['route' => 'attendance.index', 'match' => 'attendance.*', 'label' => 'Attendance', 'icon' => 'clipboard', 'roles' => ['admin', 'teacher']],
        ['route' => 'payments.index', 'match' => 'payments.*', 'label' => 'Fees & M-Pesa', 'icon' => 'banknotes', 'roles' => ['admin']],
    ];
@endphp

<div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-slate-950 transition-transform duration-200 lg:translate-x-0">
    <div class="dot-pattern absolute inset-x-0 top-0 h-48 opacity-60"></div>
    <div class="relative flex items-center gap-3 px-6 py-6">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-700 text-lg font-bold text-white shadow-lg shadow-brand-600/40">A</span>
        <div>
            <p class="text-lg font-bold leading-none tracking-tight text-white">{{ config('app.name') }}</p>
            <p class="mt-1 text-xs text-slate-400">School management</p>
        </div>
    </div>

    <nav class="relative flex-1 space-y-1 px-4 py-2">
        <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Menu</p>
        @foreach ($nav as $item)
            @if (in_array($user->role, $item['roles'], true))
                <a href="{{ route($item['route']) }}" @class(['nav-link', 'nav-link-active' => request()->routeIs($item['match'])])>
                    <x-icon :name="$item['icon']" /> {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>

    <div class="relative m-4 rounded-2xl bg-white/5 p-4">
        <div class="flex items-center gap-3">
            <x-avatar :name="$user->name" />
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                <p class="truncate text-xs capitalize text-slate-400">{{ $user->role }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-white/5 px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                <x-icon name="logout" class="h-4 w-4" /> Sign out
            </button>
        </form>
    </div>
</aside>

<div class="lg:pl-72">
    <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200/70 bg-white/80 px-4 py-3 backdrop-blur lg:hidden">
        <button type="button" data-sidebar-toggle class="rounded-lg p-2 text-slate-600 hover:bg-slate-100" aria-label="Open menu"><x-icon name="menu" /></button>
        <span class="font-bold tracking-tight text-slate-900">{{ config('app.name') }}</span>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success') || session('error'))
            <div class="toast mb-6 flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-medium {{ session('success') ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}" role="status">
                <x-icon :name="session('success') ? 'check-circle' : 'exclamation'" class="h-5 w-5 shrink-0" />
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
