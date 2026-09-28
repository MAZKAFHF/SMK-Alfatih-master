<x-portal.layouts.app :title="'Portal PPDB'">
<div class="flex flex-wrap items-end justify-between gap-3">
<div>
<p class="text-xs font-bold uppercase tracking-widest text-primary-700">PPDB {{ $period?->academic_year ?? '' }} — {{ $availability->publicLabel() }}</p>
<h1 class="mt-1 font-display text-2xl font-extrabold">Halo, {{ auth()->user()->name }}</h1>
<p class="text-sm text-slate-500">Kelola pendaftaran semua anak. @unless(auth()->user()->hasVerifiedEmail()) <span class="font-semibold text-amber-700">Email belum terverifikasi — verifikasi sebelum kirim final.</span> <form method="POST" novalidate action="{{ route('portal.verification.resend') }}" class="inline">@csrf<button class="underline">Kirim ulang</button></form>@endunless</p>
</div>
@if($canRegister)
<a href="{{ route('portal.applications.create') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">+ Tambah Calon Siswa</a>
@else
<div class="max-w-md rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300" role="status">{{ $availability->portalNotice() }}</div>
@endif
</div>

@if($current->isEmpty() && $history->isEmpty())
<div class="mt-6"><x-ui.card class="p-8 text-center"><h2 class="font-bold">Belum ada pendaftaran</h2><p class="mt-1 text-sm text-slate-500">{{ $canRegister ? 'Tambahkan calon siswa pertama untuk memulai.' : $availability->portalNotice() }}</p>@if($canRegister)<div class="mt-4"><x-ui.button href="{{ route('portal.applications.create') }}">+ Tambah Calon Siswa</x-ui.button></div>@endif</x-ui.card></div>
@else
@if($current->isNotEmpty())
<h2 class="mt-6 text-sm font-bold uppercase tracking-wider text-slate-500">Saat ini — PPDB {{ $period?->academic_year }}</h2>
<div class="mt-3 grid gap-4 md:grid-cols-2">
@foreach($current as $app)
<x-ui.card class="p-5">
<div class="flex items-start justify-between gap-3">
<div class="min-w-0"><p class="truncate font-bold text-slate-900 dark:text-white">{{ $app->name }}</p><p class="font-mono text-xs text-slate-500">{{ $app->registration_number }} &bull; {{ $app->program?->name }} &bull; {{ $app->period?->academic_year }}</p></div>
<x-ui.badge :color="$app->application_status->badgeColor()" size="sm">{{ $app->application_status->label() }}</x-ui.badge>
</div>
<ol class="mt-4 flex items-center gap-1 text-[11px]" aria-label="Progres {{ $app->name }}">
@foreach($app->progressSteps() as $step)
<li class="flex flex-1 items-center gap-1"><span class="inline-flex size-5 items-center justify-center rounded-full text-[10px] font-bold {{ $step['state']==='done' ? 'bg-emerald-500 text-white' : ($step['state']==='current' ? 'bg-primary-600 text-white' : ($step['state']==='attention' ? 'bg-red-500 text-white' : 'bg-slate-200 text-slate-500')) }}">{{ $step['state']==='done' ? '✓' : $loop->iteration }}</span><span class="hidden sm:inline">{{ $step['label'] }}</span></li>
@endforeach
</ol>
<p class="mt-3 text-xs text-slate-500">Terakhir diperbarui {{ $app->updated_at->diffForHumans() }}</p>
@php($nextAction = $app->nextPortalAction())
<div class="mt-4 flex flex-wrap gap-2"><x-ui.button size="sm" href="{{ $nextAction['route'] }}">{{ $nextAction['label'] }}</x-ui.button><x-ui.button size="sm" variant="outline" href="{{ route('portal.applications.show', $app) }}">Lihat Tahapan</x-ui.button></div>
</x-ui.card>
@endforeach
</div>
@endif

@if($history->isNotEmpty())
<h2 class="mt-8 text-sm font-bold uppercase tracking-wider text-slate-500">Riwayat</h2>
<div class="mt-3 grid gap-4 md:grid-cols-2">
@foreach($history as $app)
<x-ui.card class="p-5 opacity-95">
<div class="flex items-start justify-between gap-3">
<div class="min-w-0"><p class="truncate font-bold text-slate-900 dark:text-white">{{ $app->name }}</p><p class="font-mono text-xs text-slate-500">{{ $app->registration_number }} &bull; {{ $app->program?->name }} &bull; {{ $app->period?->academic_year ?? '—' }}</p></div>
<div class="flex shrink-0 flex-col items-end gap-1"><x-ui.badge :color="$app->application_status->badgeColor()" size="sm">{{ $app->application_status->label() }}</x-ui.badge><x-ui.badge color="slate" size="sm">Riwayat</x-ui.badge></div>
</div>
<div class="mt-4"><x-ui.button size="sm" variant="outline" href="{{ route('portal.applications.show', $app) }}">Lihat Riwayat</x-ui.button></div>
</x-ui.card>
@endforeach
</div>
@endif
@endif

@if($notifications->isNotEmpty())
<div class="mt-8"><h2 class="font-bold">Notifikasi terbaru @if($unread)<span class="rounded-full bg-red-500 px-2 py-0.5 text-xs text-white">{{ $unread }} baru</span>@endif</h2>
<ul class="mt-3 space-y-2">@foreach($notifications as $n)<li class="rounded-xl border p-3 text-sm {{ $n->read_at ? '' : 'border-primary-200 bg-primary-50' }}"><strong>{{ $n->title }}</strong><br><span class="text-slate-500">{{ $n->message }}</span></li>@endforeach</ul>
</div>
@endif
</x-portal.layouts.app>
