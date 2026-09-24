@props([
    'label' => 'Gambar',
    'name' => 'image',
    'value' => null, // URL or path
    'help' => null,
    'required' => false,
    'accept' => 'image/*',
    'maxSize' => 'Max 4MB, JPG/PNG/WEBP',
])

@php
    $error = $errors->first($name);
    $hasError = $error ? true : false;
@endphp

<div>
    @if($label)
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $label }}
            @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    @if($value)
        <div class="mb-3">
            <p class="mb-1.5 text-xs font-medium text-slate-500">Gambar saat ini:</p>
            <img src="{{ $value }}" alt="Preview" class="h-32 w-auto rounded-lg border object-cover" loading="lazy" />
        </div>
    @endif

    <input
        type="file"
        name="{{ $name }}"
        accept="{{ $accept }}"
        class="block w-full rounded-lg border bg-white text-sm text-slate-900 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:bg-slate-900 dark:text-white dark:file:bg-slate-800 dark:file:text-slate-300 {{ $hasError ? 'border-red-300 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-primary-500 focus:ring-primary-200' }}"
        @if($required && !$value) required @endif
        aria-describedby="{{ $help ? $name.'-help' : '' }} {{ $hasError ? $name.'-error' : '' }}"
    />

    @if($help || $maxSize)
        <p id="{{ $name }}-help" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
            {{ $help ?? '' }} @if($help && $maxSize) • @endif {{ $maxSize }}
        </p>
    @endif

    @if($error)
        <p id="{{ $name }}-error" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
</div>
