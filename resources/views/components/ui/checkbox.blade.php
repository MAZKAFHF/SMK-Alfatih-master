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
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @required($required)
            @if ($error) aria-invalid="true" @endif
            {{ $attributes->class([
                'size-4 shrink-0 cursor-pointer rounded border-slate-300 text-primary-600 shadow-sm dark:border-slate-600 dark:bg-slate-700',
                'focus:ring-2 focus:ring-primary-500 focus:ring-offset-0',
                'checked:bg-primary-600 checked:border-primary-600',
                $error ? 'border-red-300' : '',
            ]) }}
        >
    </div>
    <div class="ml-3 text-sm">
        <label for="{{ $id }}" class="font-medium text-slate-700 dark:text-slate-300 {{ $description ? 'cursor-pointer' : '' }}">
            {{ $label }}
        </label>
        @if ($description)
            <p class="mt-0.5 text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
</div>
