@props([
    'status' => 'draft',
    'publishedAt' => null,
])

@php
    $statusLabels = [
        'draft' => 'Draft — tidak tampil di publik',
        'published' => 'Diterbitkan — tampil di publik jika jadwal terpenuhi',
        'archived' => 'Diarsipkan — tidak tampil di publik',
        'active' => 'Aktif — tampil di publik',
        'inactive' => 'Nonaktif — tidak tampil di publik',
    ];
    $help = $statusLabels[$status] ?? $status;
@endphp

<div class="rounded-[14px] p-4" style="border: 1px solid color-mix(in srgb, var(--ctl-warn) 40%, transparent); background: var(--ctl-warn-soft);">
    <h4 class="text-sm font-semibold" style="color: var(--ctl-text);">Status Publikasi</h4>
    <p class="ctl-muted mt-1 text-xs">{{ $help }}</p>
    @if($publishedAt)
        <p class="mt-2 text-xs font-medium" style="color: var(--ctl-text);">Jadwal: {{ \Carbon\Carbon::parse($publishedAt)->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB</p>
    @endif
    <div class="mt-3">
        {{ $slot }}
    </div>
</div>
