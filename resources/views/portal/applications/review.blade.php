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
<p class="mt-1 text-xs text-slate-500">Draf boleh belum lengkap, tetapi pengiriman final memeriksa semuanya di server.</p>
<ul class="mt-3 space-y-2 text-sm">
@foreach(\App\Services\FinalSubmissionCheck::sections() as $section => $fields)
@php $missing = ($gate['missing_sections'][$section] ?? []); @endphp
<li class="flex items-start gap-2 rounded-lg border px-3 py-2 {{ $missing ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50/50' }}">
<span aria-hidden="true" class="mt-0.5 inline-flex size-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $missing ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white' }}">{{ $missing ? '!' : '✓' }}</span>
<span><strong>{{ $section }}</strong>@if($missing)<br><span class="text-xs text-amber-700">{{ count($missing) }} data belum lengkap</span>@else<br><span class="text-xs text-slate-500">Lengkap</span>@endif</span>
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
