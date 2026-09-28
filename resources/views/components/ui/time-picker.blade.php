@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'help' => null,
    'required' => false,
    'step' => 30,
])

@php
    $id = $id ?? $name;
    $error = $name ? $errors->first($name) : null;
    $iso = is_string($value) && preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', (string) $value) ? substr((string) $value, 0, 5) : '';
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

    <div class="relative" data-ctl-timepicker data-step="{{ $step }}">
        <input type="hidden" name="{{ $name }}" value="{{ $iso }}" />
        <div class="ctl-input flex items-center gap-2 !p-0 !pl-3.5">
            <input
                type="text"
                id="{{ $id }}-display"
                data-ctl-time-display
                value=""
                placeholder="HH:mm"
                autocomplete="off"
                inputmode="numeric"
                aria-label="{{ $label ?? 'Jam' }} (format 24 jam: HH:mm)"
                @if ($error) aria-invalid="true" @endif
                @if ($name) aria-describedby="{{ $error ? $id.'-error' : ($help ? $id.'-help' : null) }}" @endif
                class="min-w-0 flex-1 bg-transparent py-2.5 font-mono text-sm outline-none placeholder:text-[var(--ctl-input-placeholder)]"
            />
            <button
                type="button"
                data-ctl-trigger="{{ $id }}-times"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-label="Buka pilihan {{ $label ?? 'jam' }}"
                class="ctl-btn ctl-btn-ghost shrink-0 !p-2.5"
            >
                <svg class="size-4" style="color: var(--ctl-faint);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>

        <div
            id="{{ $id }}-times"
            data-ctl-popover
            class="ctl-popover absolute z-50 mt-1.5 hidden w-full min-w-[160px]"
        ></div>
    </div>

    @if ($error)
        <p role="alert" id="{{ $id }}-error" class="ctl-error-text">{{ $error }}</p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="ctl-help">{{ $help }}</p>
    @endif
</div>
