@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'required' => false,
    'autofocus' => false,
    'readonly' => false,
    'prefix' => null,
    'suffix' => null,
])

@php
    $id = $id ?? $name;
    $error = $name ? $errors->first($name) : null;
    $hasError = $error ? true : false;
    $isPassword = strtolower((string) $type) === 'password';
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="ctl-label">
            {{ $label }}
            @if ($required)
                <span class="ctl-req" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="flex items-stretch">
        @if ($prefix)
            <span
                class="inline-flex items-center rounded-l-[10px] px-3.5 py-2.5 text-sm"
                style="border: 1px solid var(--ctl-input-border); border-right: none; background: var(--ctl-sunken); color: var(--ctl-muted);"
            >{{ $prefix }}</span>
        @endif

        <div class="relative min-w-0 flex-1">
            <input
                type="{{ $type }}"
                id="{{ $id }}"
                name="{{ $name }}"
                value="{{ $value }}"
                placeholder="{{ $placeholder }}"
                @required($required)
                {{ $autofocus ? 'autofocus' : '' }}
                @readonly($readonly)
                {{ $hasError ? 'aria-invalid="true"' : '' }}
                @if ($name) aria-describedby="{{ $hasError ? $id.'-error' : ($help ? $id.'-help' : null) }}" @endif
                {{ $attributes->class(['ctl-input', $prefix ? '!rounded-l-none !border-l-0 !pl-0' : '', $suffix ? '!rounded-r-none !border-r-0 !pr-0' : '', $isPassword ? '!pr-12' : ''])->merge([]) }}
            />

            @if ($isPassword)
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 z-10 inline-flex w-12 items-center justify-center rounded-r-[10px] text-slate-500 transition hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500 dark:text-slate-400 dark:hover:text-white"
                    data-password-toggle
                    aria-controls="{{ $id }}"
                    aria-label="Tampilkan kata sandi"
                    aria-pressed="false"
                >
                    <svg data-password-icon-show aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6S2.25 12 2.25 12Z" />
                        <circle cx="12" cy="12" r="2.75" />
                    </svg>
                    <svg data-password-icon-hide aria-hidden="true" class="hidden size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.16A10.9 10.9 0 0 1 12 6c6.25 0 9.75 6 9.75 6a17.8 17.8 0 0 1-2.08 2.82M14.12 14.12A3 3 0 0 1 9.88 9.88M6.61 6.61C3.77 8.35 2.25 12 2.25 12s3.5 6 9.75 6a10.5 10.5 0 0 0 3.39-.55" />
                    </svg>
                </button>
            @endif
        </div>

        @if ($suffix)
            <span
                class="inline-flex items-center rounded-r-[10px] px-3.5 py-2.5 text-sm"
                style="border: 1px solid var(--ctl-input-border); border-left: none; background: var(--ctl-sunken); color: var(--ctl-muted);"
            >{{ $suffix }}</span>
        @endif
    </div>

    @if ($error)
        <p role="alert" id="{{ $id }}-error" class="ctl-error-text">{{ $error }}</p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="ctl-help">{{ $help }}</p>
    @endif
</div>
