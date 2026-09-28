@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'required' => false,
    'autofocus' => false,
])

@php
    $id = $id ?? $name;
    $error = $name ? $errors->first($name) : null;
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

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows ?? 3 }}"
        placeholder="{{ $placeholder }}"
        @required($required)
        {{ $autofocus ? 'autofocus' : '' }}
        @if ($error) aria-invalid="true" @endif
        {{ $attributes->class(['ctl-input'])->merge([]) }}
    >{{ $slot }}{{ $value }}</textarea>

    @if ($error)
        <p role="alert" id="{{ $id }}-error" class="ctl-error-text">{{ $error }}</p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="ctl-help">{{ $help }}</p>
    @endif
</div>
