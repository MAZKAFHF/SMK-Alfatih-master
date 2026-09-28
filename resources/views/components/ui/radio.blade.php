@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => '1',
    'checked' => false,
    'description' => null,
    'required' => false,
])

@php
    $id = $id ?? ($name . '_' . Str::slug($value));
    $error = $name ? $errors->first($name) : null;
@endphp

<div class="relative flex items-start">
    <div class="flex h-5 items-center">
        <input
            type="radio"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @required($required)
            @if ($error) aria-invalid="true" @endif
            {{ $attributes->class(['ctl-radio', '!size-[18px]']) }}
        >
    </div>
    <div class="ml-3 text-sm">
        <label for="{{ $id }}" class="font-medium {{ $description ? 'cursor-pointer' : '' }}" style="color: var(--ctl-text);">
            {{ $label }}
        </label>
        @if ($description)
            <p class="ctl-muted mt-0.5">{{ $description }}</p>
        @endif
        @if ($error)
            <p role="alert" class="ctl-error-text">{{ $error }}</p>
        @endif
    </div>
</div>
