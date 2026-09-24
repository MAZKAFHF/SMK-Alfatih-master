@props([
    'label' => null,
    'name' => null,
    'value' => null,
    'help' => null,
    'required' => false,
    'placeholder' => null,
    'id' => null,
])

@php
    $id = $id ?? $name ?? 'rich-'.uniqid();
    $error = $name ? $errors->first($name) : null;
    $hasError = $error ? true : false;
    $inputId = $id.'-input';
    // Value should be sanitized HTML, but we need to output as is for editor
    $initialValue = old($name, $value ?? '');
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if($required)<span class="text-red-500" aria-hidden="true">*</span>@endif
        </label>
    @endif

    @if($help)
        <p id="{{ $id }}-help" class="mb-2 text-xs text-slate-500 dark:text-slate-400">{{ $help }}</p>
    @endif

    <input id="{{ $inputId }}" type="hidden" name="{{ $name }}" value="{{ $initialValue }}">

    <trix-editor
        input="{{ $inputId }}"
        class="trix-content rounded-lg border bg-white min-h-[180px] text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 dark:bg-slate-900 dark:text-white {{ $hasError ? 'border-red-300 focus:border-red-500 focus:ring-red-200 dark:border-red-600' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-200 dark:border-slate-600' }}"
        placeholder="{{ $placeholder }}"
        @if($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @elseif($help) aria-describedby="{{ $id }}-help" @endif
    ></trix-editor>

    @if($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @elseif($help)
        {{-- help already above --}}
    @endif
</div>
