<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white">
<div class="grid min-h-screen lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-slate-950 lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div class="dot-pattern absolute inset-0"></div>
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-brand-600/40 blur-3xl"></div>
        <div class="absolute -right-24 bottom-0 h-96 w-96 rounded-full bg-fuchsia-600/30 blur-3xl"></div>

        <div class="relative flex items-center gap-3">
            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-700 text-xl font-bold text-white shadow-lg shadow-brand-600/40">A</span>
            <span class="text-xl font-bold tracking-tight text-white">{{ config('app.name') }}</span>
        </div>

        <div class="relative">
            <h1 class="max-w-md text-4xl font-bold leading-tight tracking-tight text-white">Run your whole school from one calm dashboard.</h1>
            <p class="mt-4 max-w-md text-slate-300">Students, teachers, classes, daily attendance and fee collection through M-Pesa — all in one place.</p>
            <div class="mt-8 grid max-w-md grid-cols-3 gap-3">
                @foreach ([['Students', 'users'], ['Attendance', 'clipboard'], ['M-Pesa fees', 'banknotes']] as [$t, $i])
                    <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                        <x-icon :name="$i" class="h-6 w-6 text-brand-300" />
                        <p class="mt-3 text-sm font-medium text-white">{{ $t }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="relative text-sm text-slate-500">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>

    <div class="flex items-center justify-center px-6 py-12">
        <div class="fade-up w-full max-w-sm">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-bold text-white">A</span>
                <span class="text-lg font-bold tracking-tight">{{ config('app.name') }}</span>
            </div>

            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Welcome back</h2>
            <p class="mt-2 text-sm text-slate-500">Sign in to continue to your dashboard.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <x-field label="Email" name="email" type="email" autocomplete="email" placeholder="you@school.ke" required autofocus />
                <x-field label="Password" name="password" type="password" autocomplete="current-password" placeholder="••••••••" required />

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-400"> Keep me signed in
                </label>

                <button class="btn-primary w-full py-3">Sign in</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
