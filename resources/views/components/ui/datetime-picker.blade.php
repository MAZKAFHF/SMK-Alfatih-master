@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'value' => null,
    'help' => null,
    'required' => false,
])

@php
    $id = $id ?? $name;
    $error = $name ? $errors->first($name) : null;
    // Nilai mesin: YYYY-MM-DDTHH:mm (Asia/Jakarta). Tampil: "25 Sep 2026 • 08:00".
    try {
        $dt = $value ? \Carbon\Carbon::parse($value, 'Asia/Jakarta') : null;
    } catch (\Throwable $e) {
        $dt = null;
    }
    $dateIso = $dt?->format('Y-m-d') ?? '';
    $timeIso = $dt?->format('H:i') ?? '';
    $display = $dt ? $dt->translatedFormat('d M Y').' • '.$dt->format('H:i') : '';
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

    <input type="hidden" name="{{ $name }}" value="{{ $dt?->format('Y-m-d\TH:i') ?? '' }}" data-ctl-datetime-value="{{ $id }}" />
    <div class="grid grid-cols-2 gap-3" role="group" aria-labelledby="{{ $id }}-label">
        <div data-ctl-datetime-part="date" data-ctl-datetime-for="{{ $id }}">
            <x-ui.date-picker :id="$id.'-date'" :value="$dateIso" help="" />
        </div>
        <div data-ctl-datetime-part="time" data-ctl-datetime-for="{{ $id }}">
            <x-ui.time-picker :id="$id.'-time'" :value="$timeIso" />
        </div>
    </div>
    <p class="ctl-help mt-1.5">Tampil: {{ $display ?: '—' }} (WIB). Tersimpan sebagai waktu Jakarta.</p>

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
    const part = e.target.closest('[data-ctl-datetime-part]');
    if (!part) return;
    const id = part.dataset.ctlDatetimeFor;
    const scope = part.closest('[data-ctl-datetime-value]')?.parentElement ?? document;
    const hidden = document.querySelector(`[data-ctl-datetime-value="${id}"]`);
    if (!hidden) return;
    const wrap = hidden.parentElement;
    const dateVal = wrap.querySelector('[data-ctl-datetime-part="date"] input[type="hidden"]')?.value ?? '';
    const timeVal = wrap.querySelector('[data-ctl-datetime-part="time"] input[type="hidden"]')?.value ?? '';
    hidden.value = dateVal ? (dateVal + (timeVal ? 'T' + timeVal : '')) : '';
});
</script>
@endpush
@endonce
