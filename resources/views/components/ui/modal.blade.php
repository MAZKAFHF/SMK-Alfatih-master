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
            class="ctl-popover relative w-full {{ $sizes[$size] }} !rounded-2xl"
            role="document"
        >
            <div class="flex items-start justify-between gap-4 px-6 py-4" style="border-bottom: 1px solid var(--ctl-border);">
                <div>
                    @if ($title)
                        <h3 id="{{ $id }}-title" class="text-lg font-semibold" style="color: var(--ctl-text);">{{ $title }}</h3>
                    @endif
                    @isset($subtitle)
                        <p class="ctl-muted mt-0.5 text-sm">{{ $subtitle }}</p>
                    @endisset
                </div>
                <button
                    type="button"
                    data-modal-close
                    class="ctl-btn ctl-btn-ghost !p-1.5"
                    aria-label="Tutup"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto px-6 py-5 ctl-scrollbar" style="color: var(--ctl-text);">
                {{ $slot }}
            </div>

            @if ($footer)
                <div class="flex items-center justify-end gap-3 px-6 py-4" style="border-top: 1px solid var(--ctl-border);">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
