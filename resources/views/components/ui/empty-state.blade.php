@props([
    'icon' => true,
    'title' => 'Belum ada data',
    'description' => null,
    'action' => null,
])

    <div {{ $attributes->class(['flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50/50 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-800/50']) }}>
        @if ($icon === true)
            <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-white shadow-card dark:bg-slate-800">
                <svg class="size-7 text-slate-400 dark:text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        </div>
    @else
        <div class="mb-4">{{ $icon }}</div>
    @endif

    <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 max-w-md text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif

    @if ($action)
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
