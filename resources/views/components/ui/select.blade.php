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
])

@php
    $id = $id ?? $name;
    $error = $name ? $errors->first($name) : null;
    $base = 'block w-full appearance-none rounded-lg border bg-white text-sm text-slate-900 shadow-sm transition-colors dark:bg-slate-900 dark:text-white';
    $base .= ' disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed dark:disabled:bg-slate-800 dark:disabled:text-slate-500';
    $base .= ' focus:outline-none focus:ring-2';
    $base .= $error
        ? ' border-red-300 focus:border-red-500 focus:ring-red-200 dark:border-red-600'
        : ' border-slate-300 focus:border-primary-500 focus:ring-primary-200 dark:border-slate-600 dark:focus:border-primary-500 dark:focus:ring-primary-800';
    $base .= ' py-2.5 pl-3.5 pr-10';
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            @required($required)
            @if ($error) aria-invalid="true" @endif
            {{ $attributes->class([$base, $attributes->get('class')]) }}
        >
            @if ($placeholderOption)
                <option value="" {{ $value === null || $value === '' ? 'selected' : '' }} disabled>
                    {{ $placeholder ?? '— Pilih —' }}
                </option>
            @endif

            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach

            {{ $slot }}
        </select>

        <svg
            class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400 dark:text-slate-500"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
        >
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </div>

    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{{ $help }}</p>
    @endif
</div>
