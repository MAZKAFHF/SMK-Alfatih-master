@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'help' => null,
    'required' => false,
    'min' => null,
    'max' => null,
    'clearable' => false,
    'minYear' => null,
    'maxYear' => null,
])

@php
    $id = $id ?? $name;
    $error = $name ? $errors->first($name) : null;
    // Parser deterministik: jangan izinkan Carbon menggulung 31-02 ke bulan berikutnya.
    $iso = '';
    $rawValue = trim((string) ($value ?? ''));
    foreach (['!Y-m-d', '!d-m-Y'] as $format) {
        if ($rawValue === '') break;
        $parsed = \Carbon\CarbonImmutable::createFromFormat($format, $rawValue);
        $parseErrors = \Carbon\CarbonImmutable::getLastErrors();
        $expected = $parsed ? ($format === '!Y-m-d' ? $parsed->format('Y-m-d') : $parsed->format('d-m-Y')) : null;
        if ($parsed && (!is_array($parseErrors) || ($parseErrors['warning_count'] === 0 && $parseErrors['error_count'] === 0)) && $expected === $rawValue) {
            $iso = $parsed->format('Y-m-d');
            break;
        }
    }
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}-display" class="ctl-label">
            {{ $label }}
            @if ($required)
                <span class="ctl-req" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative" data-ctl-datepicker data-min="{{ $min }}" data-max="{{ $max }}" data-min-year="{{ $minYear }}" data-max-year="{{ $maxYear }}" data-clearable="{{ $clearable ? '1' : '0' }}">
        <input type="hidden" name="{{ $name }}" value="{{ $iso }}" />
        <div class="ctl-input flex items-center gap-2 !p-0 !pl-3.5" data-ctl-datebox>
            <input
                type="text"
                id="{{ $id }}-display"
                data-ctl-date-display
                value=""
                placeholder="DD-MM-YYYY"
                autocomplete="off"
                inputmode="numeric"
                aria-label="{{ $label ?? 'Tanggal' }} (format: DD-MM-YYYY)"
                @if ($error) aria-invalid="true" @endif
                @if ($name) aria-describedby="{{ $error ? $id.'-error' : ($help ? $id.'-help' : null) }}" @endif
                class="min-w-0 flex-1 bg-transparent py-2.5 text-sm outline-none placeholder:text-[var(--ctl-input-placeholder)]"
            />
            <button
                type="button"
                data-ctl-trigger="{{ $id }}-cal"
                aria-haspopup="dialog"
                aria-expanded="false"
                aria-label="Buka kalender {{ $label ?? 'tanggal' }}"
                class="ctl-btn ctl-btn-ghost shrink-0 !p-2.5"
            >
                <svg class="size-4" style="color: var(--ctl-faint);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </button>
        </div>

        <div
            id="{{ $id }}-cal"
            data-ctl-popover
            role="dialog"
            aria-modal="false"
            aria-label="Pilih {{ $label ?? 'tanggal' }}"
            class="ctl-popover absolute z-50 mt-1.5 hidden w-[300px] max-w-[calc(100vw-2rem)]"
        ></div>
    </div>

    @if ($error)
        <p role="alert" id="{{ $id }}-error" class="ctl-error-text">{{ $error }}</p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="ctl-help">{{ $help }}</p>
    @endif
</div>
