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

<div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
    <h4 class="text-sm font-semibold text-amber-900 dark:text-amber-200">Status Publikasi</h4>
    <p class="mt-1 text-xs text-amber-800 dark:text-amber-300">{{ $help }}</p>
    @if($publishedAt)
        <p class="mt-2 text-xs font-medium text-amber-900 dark:text-amber-200">Jadwal: {{ \Carbon\Carbon::parse($publishedAt)->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i') }} WIB</p>
    @endif
    <div class="mt-3">
        {{ $slot }}
    </div>
</div>
