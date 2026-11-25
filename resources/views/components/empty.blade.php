@props(['icon' => 'search', 'title', 'text' => null])
<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <span class="mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><x-icon :name="$icon" class="h-7 w-7" /></span>
    <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
    @if ($text)<p class="mt-1 max-w-sm text-sm text-slate-500">{{ $text }}</p>@endif
    <div class="mt-5">{{ $slot }}</div>
</div>
