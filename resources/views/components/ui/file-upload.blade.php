@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'accept' => null,
    'acceptAttr' => null,
    'help' => null,
    'required' => false,
    'previewUrl' => null,
    'fileName' => null,
    'maxNote' => null,
    'buttonText' => 'Pilih Dokumen',
])

@php
    // ID unik wajib: fallback acak agar kartu ganda tidak pernah berbagi target.
    $id = $id ?? ($name ? $name.'-' : 'file-').substr(md5(uniqid('', true)), 0, 8);
    $error = $name ? $errors->first($name) : null;
@endphp

<div>
    @if ($label)
        <span class="ctl-label" id="{{ $id }}-label">
            {{ $label }}
            @if ($required)
                <span class="ctl-req" aria-hidden="true">*</span>
            @endif
        </span>
    @endif

    <label
        for="{{ $id }}-file"
        class="ctl-file-drop"
        @if ($error) data-error="true" @endif
    >
        @if ($previewUrl)
            <img src="{{ $previewUrl }}" alt="Pratinjau {{ $label ?? 'dokumen' }}" class="max-h-32 rounded-lg object-contain" />
        @else
            <span class="flex size-11 items-center justify-center rounded-full" style="background: var(--ctl-primary-soft); color: var(--ctl-primary);" aria-hidden="true">
                <x-admin.icon name="upload" class="size-5" />
            </span>
        @endif
        <span class="text-sm font-semibold" style="color: var(--ctl-text);">
            {{ $fileName ?? $buttonText }}
        </span>
        <span class="ctl-faint text-xs">
            @if ($accept || $maxNote)
                {{ $accept ? 'Format: '.$accept.' • ' : '' }}{{ $maxNote ?? '' }}
            @else
                Klik untuk memilih file
            @endif
        </span>
        @if ($fileName)
            <span class="text-xs font-semibold" style="color: var(--ctl-primary);">Klik untuk mengganti</span>
        @endif
        <input
            type="file"
            id="{{ $id }}-file"
            name="{{ $name }}"
            @if ($acceptAttr) accept="{{ $acceptAttr }}" @endif
            @required($required)
            @if ($error) aria-invalid="true" @endif
            aria-describedby="{{ $error ? $id.'-error' : ($help ? $id.'-help' : null) }}"
            class="sr-only"
            data-ctl-file-input
            {{ $attributes->except(['class'])->merge([]) }}
        />
    </label>

    @if ($error)
        <p role="alert" id="{{ $id }}-error" class="ctl-error-text">{{ $error }}</p>
    @elseif ($help)
        <p id="{{ $id }}-help" class="ctl-help">{{ $help }}</p>
    @endif
</div>

@once
@push('scripts')
<script>
document.addEventListener('change', (e) => {
    const input = e.target.closest('[data-ctl-file-input]');
    if (!input || !input.files?.length) return;
    const label = input.closest('label');
    const nameEl = label?.querySelector('span.text-sm.font-semibold');
    if (nameEl) nameEl.textContent = input.files.length > 1 ? `${input.files.length} file dipilih` : input.files[0].name;
});
</script>
@endpush
@endonce
