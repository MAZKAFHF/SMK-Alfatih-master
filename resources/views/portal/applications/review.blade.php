<x-portal.layouts.app :title="'Review — '.$application->name">
<h1 class="font-display text-xl font-extrabold">Review Pendaftaran</h1>
<p class="text-sm text-slate-500">Periksa kembali sebelum kirim final. Setelah dikirim, data penting terkunci.</p>
<x-ui.card class="mt-4 p-5">
<dl class="grid gap-3 sm:grid-cols-2 text-sm">
<div><dt class="text-xs uppercase text-slate-400">Nama</dt><dd class="font-semibold">{{ $application->name }}</dd></div>
<div><dt class="text-xs uppercase text-slate-400">Program</dt><dd class="font-semibold">{{ $application->program?->name }}</dd></div>
<div><dt class="text-xs uppercase text-slate-400">TTL</dt><dd>{{ $application->birth_place }}, {{ $application->birth_date?->format('d M Y') }}</dd></div>
<div><dt class="text-xs uppercase text-slate-400">Asal Sekolah</dt><dd>{{ $application->school_origin }}</dd></div>
<div><dt class="text-xs uppercase text-slate-400">Ayah</dt><dd>{{ $application->father_name }} ({{ $application->father_phone }})</dd></div>
<div><dt class="text-xs uppercase text-slate-400">Ibu</dt><dd>{{ $application->mother_name }} ({{ $application->mother_phone }})</dd></div>
</dl>
<h2 class="mt-4 font-bold">Dokumen</h2>
<ul class="text-sm">@foreach($application->documents as $d)<li>{{ $d->type->label() }} — {{ $d->path ? 'terunggah' : 'BELUM' }}</li>@endforeach</ul>
</x-ui.card>

<x-ui.card class="mt-4 p-5">
<h2 class="font-bold">Kelengkapan Pengiriman</h2>
@php
    $fieldLabels = \App\Services\FinalSubmissionCheck::labels();
    $missingCount = collect($gate['missing_sections'])->flatten()->unique()->count();
@endphp
@if(!$gate['valid'])
<div role="alert" aria-live="assertive" data-validation-summary tabindex="-1" class="mt-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-200">
<p class="font-bold">Masih ada {{ $missingCount }} data wajib yang perlu dilengkapi</p>
<p class="mt-1">Lihat nama data di bawah, lalu klik untuk langsung menuju bagian yang perlu diperbaiki.</p>
</div>
@else
<p class="mt-1 text-sm font-semibold text-emerald-700 dark:text-emerald-300">Semua data dan dokumen wajib sudah lengkap. Pendaftaran siap dikirim.</p>
@endif
<ul class="mt-3 space-y-2 text-sm">
@foreach(\App\Services\FinalSubmissionCheck::sections() as $section => $fields)
@php $missing = ($gate['missing_sections'][$section] ?? []); @endphp
<li class="flex items-start gap-2 rounded-lg border px-3 py-2 {{ $missing ? 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/40' : 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-900 dark:bg-emerald-950/20' }}">
<span aria-hidden="true" class="mt-0.5 inline-flex size-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $missing ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white' }}">{{ $missing ? '!' : '✓' }}</span>
<div class="min-w-0 flex-1"><strong>{{ $section }}</strong>
@if($missing)
<p class="text-xs font-semibold text-amber-700 dark:text-amber-300">{{ count($missing) }} data perlu diperbaiki:</p>
<ul class="mt-1 space-y-1">
@foreach($missing as $field)
@php
    $isDocument = str_starts_with($field, 'dokumen_');
    $documentType = $isDocument ? str_replace('dokumen_', '', $field) : null;
    $anchor = match ($field) {
        'birth_date' => 'birth_date-display',
        'gender', 'program_id' => $field.'-trigger',
        default => $field,
    };
    $fixUrl = $isDocument
        ? route('portal.applications.show', [$application, 'tahap' => 'dokumen']).'#document-'.$documentType
        : route('portal.applications.edit', $application).'#'.$anchor;
@endphp
<li>
<a href="{{ $fixUrl }}" class="inline-flex items-start gap-1.5 font-semibold text-amber-900 underline decoration-amber-400 underline-offset-2 hover:text-primary-700 dark:text-amber-100">
<span aria-hidden="true">→</span><span>{{ $fieldLabels[$field] ?? str($field)->replace('_', ' ')->title() }}: {{ $gate['errors']->first($field) }}</span>
</a>
</li>
@endforeach
</ul>
@else
<p class="text-xs text-slate-500">Lengkap</p>
@endif
</div>
</li>
@endforeach
</ul>
</x-ui.card>
<form method="POST" action="{{ route('portal.applications.submit', $application) }}" class="mt-4" novalidate>@csrf
<x-ui.checkbox label="Saya menyatakan data di atas benar dan siap dikirim untuk verifikasi." name="confirm" value="1" required />
<x-ui.validation-summary title="Belum bisa dikirim" />
<div class="mt-3 flex justify-end gap-2"><x-ui.button variant="ghost" href="{{ route('portal.applications.show', $application) }}">Kembali</x-ui.button><x-ui.button type="submit">Kirim Final</x-ui.button></div>
</form>
</x-portal.layouts.app>
