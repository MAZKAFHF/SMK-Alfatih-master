@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'required' => false,
    'options' => [],
    'placeholderOption' => true,
    'searchable' => null,
    'disabled' => false,
])

@php
    $id = $id ?? $name;
    $error = $name ? $errors->first($name) : null;
    $opts = collect($options);
    $currentLabel = $opts->has($value) && $value !== '' && $value !== null ? $opts->get($value) : null;
    $searchable = $searchable ?? ($opts->count() > 8);
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}-trigger" class="ctl-label">
            {{ $label }}
            @if ($required)
                <span class="ctl-req" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative" data-ctl-select>
        <input type="hidden" name="{{ $name }}" value="{{ $value }}" @disabled($disabled) />
        <button
            type="button"
            id="{{ $id }}-trigger"
            data-ctl-trigger="{{ $id }}-list"
            aria-haspopup="listbox"
            aria-expanded="false"
            @if ($error) aria-invalid="true" @endif
            @if ($name) aria-describedby="{{ $error ? $id.'-error' : ($help ? $id.'-help' : null) }}" @endif
            @disabled($disabled)
            class="ctl-input flex items-center justify-between gap-2 text-left {{ $currentLabel ? '' : '' }} disabled:cursor-not-allowed"
        >
            <span data-ctl-select-label class="truncate {{ $currentLabel ? '' : 'ctl-faint' }}">{{ $currentLabel ?? $placeholder ?? '— Pilih —' }}</span>
            <svg class="size-4 shrink-0" style="color: var(--ctl-faint);" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            id="{{ $id }}-list"
            data-ctl-popover
            role="listbox"
            aria-label="{{ $label ?? $name }}"
            class="ctl-popover absolute z-50 mt-1.5 hidden max-h-60 w-full overflow-y-auto p-1.5 ctl-scrollbar"
        >
            @if ($searchable)
                <div class="sticky top-0 p-1" style="background: var(--ctl-raised);">
                    <input
                        type="text"
                        data-ctl-select-search
                        placeholder="Cari..."
                        aria-label="Cari pilihan"
                        class="ctl-input !py-2 text-sm"
                    />
                </div>
            @endif
            @if ($placeholderOption)
                <button type="button" role="option" data-value="" data-label="{{ $placeholder ?? '— Pilih —' }}" aria-selected="{{ $value === '' || $value === null ? 'true' : 'false' }}" class="ctl-option ctl-faint">
                    {{ $placeholder ?? '— Pilih —' }}
                </button>
            @endif

            @foreach ($options as $optionValue => $optionLabel)
                <button
                    type="button"
                    role="option"
                    data-value="{{ $optionValue }}"
                    data-label="{{ $optionLabel }}"
                    aria-selected="{{ (string) $value === (string) $optionValue ? 'true' : 'false' }}"
                    class="ctl-option"
                >
                    <span class="min-w-0 flex-1 truncate">{{ $optionLabel }}</span>
                    <svg class="size-4 shrink-0" style="color: var(--ctl-primary); display: {{ (string) $value === (string) $optionValue ? '' : 'none' }};" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endforeach

            {{ $slot }}
        </div>
    </div>

    @if ($error)
        <p role="alert" id="{{ $id }}-error" class="ctl-error-text">{{ $error }}</p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="ctl-help">{{ $help }}</p>
    @endif
</div>
