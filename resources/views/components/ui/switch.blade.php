@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'description' => null,
])

@php
    $id = $id ?? ($name . '_switch');
    $error = $name ? $errors->first($name) : null;
@endphp

<div class="flex items-start gap-3">
    <input
        type="checkbox"
        role="switch"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($checked)
        @if ($error) aria-invalid="true" @endif
        {{ $attributes->class(['ctl-switch', 'mt-0.5']) }}
    />
    <div class="text-sm">
        <label for="{{ $id }}" class="cursor-pointer font-medium" style="color: var(--ctl-text);">
            {{ $label }}
        </label>
        @if ($description)
            <p class="ctl-muted mt-0.5">{{ $description }}</p>
        @endif
        @if ($error)
            <p class="ctl-error-text">{{ $error }}</p>
        @endif
    </div>
</div>
