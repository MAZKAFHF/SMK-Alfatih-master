@props([
    'title' => null,
    'description' => null,
])

<div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
    @if($title)
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
        @if($description)
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
        <div class="mt-4 space-y-5">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</div>
