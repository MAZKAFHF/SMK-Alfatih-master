@props(['document'])

@php
    $doc = $document;
    $typeLabel = $doc->type->label();
    $hasFile = (bool) $doc->path;
    $original = (string) ($doc->original_name ?? '');
    $ext = $original !== '' ? strtoupper(pathinfo($original, PATHINFO_EXTENSION)) : '';
    $ext = $ext !== '' ? $ext : ($doc->mime === 'application/pdf' ? 'PDF' : '');
    $sizeBytes = (int) ($doc->size ?? 0);
    $sizeLabel = '';
    if ($sizeBytes > 0) {
        if ($sizeBytes >= 1048576) {
            $sizeLabel = number_format($sizeBytes / 1048576, 1).' MB';
        } elseif ($sizeBytes >= 1024) {
            $sizeLabel = number_format($sizeBytes / 1024, 0).' KB';
        } else {
            $sizeLabel = $sizeBytes.' B';
        }
    }
    $metaParts = array_filter(['v'.(int) $doc->version, $ext, $sizeLabel]);
    $previewUrl = $hasFile ? route('admin.documents.preview', $doc) : null;
@endphp

<li class="rounded-xl border p-4" style="border-color: var(--ctl-border); background: var(--ctl-surface);">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <strong class="text-sm font-bold" style="color: var(--ctl-text);">{{ $typeLabel }}</strong>
        <x-ui.badge :color="$doc->status->badgeColor()" size="sm">{{ $doc->status->label() }}</x-ui.badge>
    </div>

    {{-- Aksi berkas: area khusus, tombol operasional yang jelas --}}
    @if($hasFile)
        <div class="mt-3 rounded-xl p-3" style="background: var(--ctl-sunken); border: 1px solid var(--ctl-border);">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg" style="background: var(--ctl-surface); border: 1px solid var(--ctl-border); color: var(--ctl-primary);" aria-hidden="true">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-bold uppercase tracking-wider" style="color: var(--ctl-faint);">Berkas terunggah</p>
                        <p class="mt-0.5 truncate text-sm font-semibold" style="color: var(--ctl-text);" title="{{ $original }}">{{ $original }}</p>
                        @if($metaParts !== [])
                            <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs" style="color: var(--ctl-muted);">
                                @foreach($metaParts as $i => $part)
                                    @if($i === 1 && $ext !== '')
                                        <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-bold" style="background: var(--ctl-surface); border: 1px solid var(--ctl-border); color: var(--ctl-muted);">{{ $part }}</span>
                                    @else
                                        <span>{{ $part }}</span>
                                    @endif
                                    @if(!$loop->last && !($i === 0))<span aria-hidden="true">•</span>@endif
                                    @if($i === 0)<span aria-hidden="true">•</span>@endif
                                @endforeach
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex shrink-0 items-center">
                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer"
                       class="ctl-btn ctl-btn-secondary ctl-btn-sm !px-4"
                       style="min-height: 44px; min-width: 44px; font-weight: 700;"
                       aria-label="Lihat berkas {{ $typeLabel }} (membuka di tab baru)">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Lihat Berkas
                        <svg class="size-3.5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="mt-3 flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm" style="background: var(--ctl-sunken); border: 1px dashed var(--ctl-border); color: var(--ctl-muted);">
            <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <span class="text-xs font-medium">Belum diunggah</span>
        </div>
    @endif

    @if($doc->admin_note)
        <p class="mt-2 text-xs font-medium" style="color: var(--ctl-warn);">Catatan: {{ $doc->admin_note }}</p>
    @endif

    <form method="POST" novalidate action="{{ route('admin.documents.review', $doc) }}" class="mt-3 grid gap-2 sm:grid-cols-[180px_1fr]" data-document-review>
        @csrf
        <x-ui.select name="status" :value="$doc->status->value" :options="['valid' => 'Valid', 'needs_revision' => 'Perlu Perbaikan']" :placeholder-option="false" />
        <x-ui.input name="admin_note" placeholder="Catatan untuk pendaftar (wajib jika revisi)" value="{{ $doc->admin_note }}" />
        <p class="text-xs font-semibold sm:col-span-2" style="color: var(--ctl-faint);" role="status" aria-live="polite" data-review-state>Tersimpan</p>
    </form>
</li>
