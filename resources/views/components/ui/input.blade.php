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
            {{ $attributes->class(['ctl-input', $prefix ? '!rounded-l-none !border-l-0 !pl-0' : '', $suffix ? '!rounded-r-none !border-r-0 !pr-0' : ''])->merge([]) }}
        />

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
