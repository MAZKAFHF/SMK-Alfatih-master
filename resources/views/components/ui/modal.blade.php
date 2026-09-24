@props([
    'id',
    'title' => null,
    'size' => 'md',
    'footer' => null,
])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

<div
    id="{{ $id }}"
    data-modal
    class="fixed inset-0 z-[70] hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
>
    <div data-modal-backdrop class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" aria-hidden="true"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div
            data-modal-panel
            class="relative w-full {{ $sizes[$size] }} rounded-2xl bg-white shadow-pop dark:bg-slate-900"
            role="document"
        >
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4 dark:border-slate-700/50">
                <div>
                    @if ($title)
                        <h3 id="{{ $id }}-title" class="text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                    @endif
                    @isset($subtitle)
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                    @endisset
                </div>
                <button
                    type="button"
                    data-modal-close
                    class="rounded-md p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300"
                    aria-label="Tutup"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto px-6 py-5 dark:text-slate-300">
                {{ $slot }}
            </div>

            @if ($footer)
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 dark:border-slate-700/50">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
