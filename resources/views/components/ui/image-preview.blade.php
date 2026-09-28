@props([
    'label' => 'Gambar',
    'name' => 'image',
    'id' => null,
    'value' => null, // URL or path
    'help' => null,
    'required' => false,
    'accept' => 'image/*',
    'maxSize' => 'Max 4MB, JPG/PNG/WEBP',
])

@php
    $id = $id ?? $name.'-'.substr(md5(uniqid('', true)), 0, 8);
    $error = $errors->first($name);
@endphp

<div>
    @if($label)
        <span class="ctl-label" id="{{ $name }}-label">
            {{ $label }}
            @if($required)<span class="ctl-req" aria-hidden="true">*</span>@endif
        </span>
    @endif

    @if($value)
        <div class="mb-3">
            <p class="ctl-faint mb-1.5 text-xs font-medium">Gambar saat ini:</p>
            <img src="{{ $value }}" alt="Pratinjau {{ $label }}" class="h-32 w-auto rounded-[10px] object-cover" style="border: 1px solid var(--ctl-border);" loading="lazy" />
        </div>
    @endif

    <label
        for="{{ $id }}"
        class="ctl-file-drop !flex-row !justify-start !gap-3 !p-4"
        @if($error) data-error="true" @endif
    >
        <span class="flex size-10 shrink-0 items-center justify-center rounded-full" style="background: var(--ctl-primary-soft); color: var(--ctl-primary);" aria-hidden="true">
            <x-admin.icon name="upload" class="size-5" />
        </span>
        <span class="min-w-0">
            <span class="block truncate text-sm font-semibold" style="color: var(--ctl-text);" data-ctl-file-name>{{ $value ? 'Ganti gambar' : 'Pilih gambar' }}</span>
            <span class="ctl-faint block text-xs">{{ $maxSize }}</span>
        </span>
        <input
            type="file"
            id="{{ $id }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            class="sr-only"
            data-ctl-file-input
            @if($required && !$value) required @endif
            @if($error) aria-invalid="true" @endif
            aria-describedby="{{ $help ? $name.'-help' : $name }} {{ $error ? $name.'-error' : '' }}"
        />
    </label>

    @if($help)
        <p id="{{ $name }}-help" class="ctl-help">{{ $help }}</p>
    @endif

    @if($error)
        <p role="alert" id="{{ $name }}-error" class="ctl-error-text">{{ $error }}</p>
    @endif
</div>

@once
@push('scripts')
<script>
document.addEventListener('change', (e) => {
    const input = e.target.closest('[data-ctl-file-input]');
    if (!input || !input.files?.length) return;
    const label = input.closest('label');
    const nameEl = label?.querySelector('[data-ctl-file-name]');
    if (nameEl) nameEl.textContent = input.files[0].name;
});
</script>
@endpush
@endonce
