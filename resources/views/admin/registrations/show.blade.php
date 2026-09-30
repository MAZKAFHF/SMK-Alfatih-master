<x-admin.layouts.app :title="'Detail Pendaftar PPDB'" :breadcrumb="[['label' => 'Pendaftar PPDB', 'url' => route('admin.registrations.index')], ['label' => $registration->name]]">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <a href="{{ route('admin.registrations.index') }}" class="ctl-muted inline-flex items-center gap-1 text-sm font-medium hover:underline">&larr; Kembali ke daftar</a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="ctl-page-title">{{ $registration->name }}</h2>
                <x-ui.badge color="sky">{{ $registration->application_status->label() }}</x-ui.badge>
            </div>
            <p class="ctl-faint mt-1 font-mono text-sm">{{ $registration->registration_number }} &bull; {{ $registration->program?->name }} &bull; {{ $registration->period?->academic_year }} &bull; sumber: {{ $registration->source }}</p>
        </div>
        <div class="flex shrink-0 gap-2"><x-ui.button variant="outline" size="sm" href="{{ route('admin.registrations.print', $registration) }}" target="_blank">Cetak Berkas</x-ui.button></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card class="p-6">
                <h3 class="ctl-section-title">Data Calon Siswa</h3>
                <dl class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    <div><dt class="ctl-faint text-xs font-medium uppercase tracking-wider">NIK / NISN</dt><dd class="mt-1 text-sm font-medium">{{ $registration->nik ?? '—' }} / {{ $registration->nisn ?? '—' }}</dd></div>
                    <div><dt class="ctl-faint text-xs font-medium uppercase tracking-wider">Jenis Kelamin</dt><dd class="mt-1 text-sm font-medium capitalize">{{ $registration->gender }}</dd></div>
                    <div><dt class="ctl-faint text-xs font-medium uppercase tracking-wider">Tempat, Tanggal Lahir</dt><dd class="mt-1 text-sm font-medium">{{ $registration->birth_place }}, {{ $registration->birth_date?->translatedFormat('d M Y') }}</dd></div>
                    <div><dt class="ctl-faint text-xs font-medium uppercase tracking-wider">Asal Sekolah</dt><dd class="mt-1 text-sm font-medium">{{ $registration->school_origin }} @if($registration->school_npsn)({{ $registration->school_npsn }})@endif</dd></div>
                    <div class="sm:col-span-2"><dt class="ctl-faint text-xs font-medium uppercase tracking-wider">Alamat</dt><dd class="mt-1 text-sm font-medium">{{ $registration->address }} {{ $registration->city ? ', '.$registration->city : '' }} {{ $registration->postal_code }}</dd></div>
                    <div><dt class="ctl-faint text-xs font-medium uppercase tracking-wider">HP / Email</dt><dd class="mt-1 text-sm font-medium">{{ $registration->phone }}<br>{{ $registration->email }}</dd></div>
                    <div><dt class="ctl-faint text-xs font-medium uppercase tracking-wider">Ayah / Ibu / Wali</dt><dd class="mt-1 text-sm font-medium">{{ $registration->father_name ?? $registration->parent_name }} ({{ $registration->father_phone }})<br>{{ $registration->mother_name }} ({{ $registration->mother_phone }})<br>{{ $registration->guardian_name }} {{ $registration->guardian_relation ? '('.$registration->guardian_relation.')' : '' }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card class="p-6">
                <h3 class="ctl-section-title">Dokumen (per-file review)</h3>
                @if($registration->documents->isEmpty())<p class="ctl-muted mt-2 text-sm">Belum ada dokumen (pendaftaran lama / manual).</p>@endif
                <ul class="mt-3 space-y-3">
                @foreach($registration->documents as $doc)
                    <x-admin.document-review-item :document="$doc" />
                @endforeach
                </ul>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    <form method="POST" novalidate action="{{ route('admin.registrations.verify', $registration) }}" class="space-y-2">@csrf<x-ui.input name="override_reason" placeholder="Alasan override (jika dokumen belum valid)" /><x-ui.button type="submit" full="true">Verifikasi Aplikasi</x-ui.button></form>
                    <form method="POST" novalidate action="{{ route('admin.registrations.revise', $registration) }}" class="space-y-2">@csrf<x-ui.input name="note" placeholder="Alasan perbaikan untuk pendaftar *" required /><x-ui.button type="submit" variant="secondary" full="true">Minta Perbaikan</x-ui.button></form>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="ctl-section-title">Wawancara</h3>
                    @php $interviewState = \App\Services\InterviewCompletion::statusForAdmin($registration); @endphp
                    <x-ui.badge :color="$interviewState['completed'] ? 'success' : 'warning'" size="sm">{{ $interviewState['label'] }}</x-ui.badge>
                </div>
                @if($interviewState['inconsistent'])
                    <p class="mt-2 rounded-lg px-3 py-2 text-xs font-semibold" style="background: var(--ctl-danger-soft); color: var(--ctl-danger);" role="alert">Data tidak konsisten: keputusan {{ $registration->decision->result->label() }} sudah ada tetapi wawancara terbaca belum selesai. Jalankan <code>php artisan ppdb:backfill-interview-completion --dry-run</code> atau periksa appointment/asesmen.</p>
                @endif
                @if($registration->appointment)
                <p class="ctl-muted mt-2 text-sm">{{ $registration->appointment->slot->date->format('d M Y') }} {{ $registration->appointment->slot->start_time }} — {{ $registration->appointment->slot->location }} ({{ $registration->appointment->status->label() }})</p>
                <form method="POST" novalidate action="{{ route('admin.appointments.complete', $registration->appointment) }}" class="mt-3 grid gap-3">@csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.select name="attendance" value="attended" :options="['attended' => 'Hadir', 'no_show' => 'Tidak Hadir']" :placeholder-option="false" />
                        <x-ui.input name="recommendation" placeholder="Rekomendasi" />
                    </div>
                    <x-ui.textarea name="interview_notes" rows="2" placeholder="Catatan interview (internal)" />
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.textarea name="tahfizh_notes" rows="2" placeholder="Catatan tahfizh (internal)" />
                        <x-ui.textarea name="tahsin_notes" rows="2" placeholder="Catatan tahsin (internal)" />
                    </div>
                    <div><x-ui.button type="submit">Simpan Kehadiran + Asesmen</x-ui.button></div>
                </form>
                @else<p class="ctl-muted mt-2 text-sm">Belum ada janji. Pendaftar memilih setelah terverifikasi via portal.</p>@endif
                @if($reschedules->isNotEmpty())
                <h4 class="mt-4 text-sm font-bold">Permohonan Reschedule</h4>
                <ul class="mt-2 space-y-2">@foreach($reschedules as $r)<li class="rounded-lg border p-2 text-xs" style="border-color: var(--ctl-border);">Alasan: {{ $r->reason }} — <strong>{{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$r->status] ?? $r->status }}</strong>@if($r->status==='pending')<form method="POST" novalidate action="{{ route('admin.reschedules.decide', $r) }}" class="mt-1 flex gap-2">@csrf<x-ui.select name="decision" value="approved" :options="['approved' => 'Setujui', 'rejected' => 'Tolak']" :placeholder-option="false" /><x-ui.button type="submit" size="sm">OK</x-ui.button></form>@endif</li>@endforeach</ul>
                @endif
            </x-ui.card>

            <x-ui.card class="p-6">
                <h3 class="ctl-section-title">Hasil & Keputusan</h3>
                @if($registration->decision)<p class="ctl-muted mt-2 text-sm">Internal: <strong>{{ $registration->decision->result->label() }}</strong> @if($registration->decision->released_at)(dirilis {{ $registration->decision->released_at->format('d M Y H:i') }})@else(belum dirilis)@endif</p>@endif
                <form method="POST" novalidate action="{{ route('admin.registrations.decide', $registration) }}" class="mt-3 grid gap-3">@csrf
                    <x-ui.select name="result" value="passed" :options="['passed' => 'Lulus', 'not_passed' => 'Belum Lulus']" :placeholder-option="false" />
                    <x-ui.input name="applicant_message" placeholder="Pesan resmi untuk pendaftar (opsional)" />
                    <x-ui.input name="internal_note" placeholder="Catatan internal (tidak terlihat pendaftar)" />
                    <x-ui.checkbox label="Saya yakin menetapkan keputusan ini." name="confirm" value="1" required />
                    <div><x-ui.button type="submit">Simpan Keputusan Internal</x-ui.button></div>
                </form>
                @if($registration->decision && !$registration->decision->released_at)
                <form method="POST" novalidate action="{{ route('admin.registrations.release', $registration) }}" class="mt-2">@csrf<x-ui.button type="submit" variant="secondary" full="true">Rilis Hasil ke Pendaftar + Email</x-ui.button></form>
                @endif
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card class="p-6">
                <h3 class="ctl-section-title">Email Terakhir</h3>
                @if($emailLog)<p class="ctl-muted mt-2 text-xs">Template: {{ $emailLog->template }}<br>Penerima: {{ $emailLog->recipient }}<br>Status: <strong>{{ $emailLog->status }}</strong> @if($emailLog->error)<br><span style="color: var(--ctl-danger);">{{ $emailLog->error }}</span>@endif</p>
                <form method="POST" novalidate action="{{ route('admin.registrations.resend', $registration) }}" class="mt-2">@csrf<x-ui.button variant="outline" size="sm" type="submit">Kirim Ulang</x-ui.button></form>
                @else<p class="ctl-muted mt-2 text-xs">Belum ada email tercatat.</p>@endif
            </x-ui.card>

            <x-ui.card class="p-6">
                <h3 class="ctl-section-title">Catatan Internal</h3>
                <form method="POST" novalidate action="{{ route('admin.registrations.note', $registration) }}" class="mt-2 space-y-2">@csrf<x-ui.textarea name="body" rows="2" required placeholder="Tidak terlihat pendaftar" /><x-ui.button type="submit" size="sm" full="true">Simpan Catatan</x-ui.button></form>
                <ul class="mt-3 space-y-2 text-xs">@foreach($registration->notes as $n)<li class="rounded-lg p-2" style="background: var(--ctl-sunken);">{{ $n->body }}<br><span class="ctl-faint">{{ $n->author?->name }} — {{ $n->created_at->diffForHumans() }}</span></li>@endforeach</ul>
            </x-ui.card>

            <x-ui.card class="p-6">
                <h3 class="ctl-section-title">Riwayat / Audit</h3>
                <ul class="mt-2 space-y-2 text-xs">@foreach($registration->history as $h)<li>{{ $h->created_at->format('d M Y H:i') }} — {{ $h->from_status ?? 'baru' }} → <strong>{{ $h->to_status }}</strong> ({{ $h->actor?->name ?? 'sistem' }})<br><span class="ctl-muted">{{ $h->note }}</span></li>@endforeach</ul>
            </x-ui.card>

            <x-ui.card class="border-red-200 p-6 dark:border-red-900/60">
                <h3 class="text-sm font-bold text-red-700 dark:text-red-400">Hapus Data Pendaftar</h3>
                <p class="ctl-muted mt-2 text-xs">Data akan dipindahkan ke Trash dan dapat dipulihkan. Masukkan kode admin 4 digit untuk melanjutkan.</p>
                <form method="POST" action="{{ route('admin.registrations.destroy', $registration) }}" class="mt-3 space-y-3">
                    @csrf
                    @method('DELETE')
                    <x-ui.input name="admin_code" type="password" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" placeholder="Kode admin 4 digit" required />
                    <x-ui.button type="submit" variant="danger" size="sm" full="true">Pindahkan ke Trash</x-ui.button>
                </form>
            </x-ui.card>

        </div>
    </div>
@push('scripts')
<script>
document.querySelectorAll('[data-document-review]').forEach((form) => {
    const status=form.querySelector('input[name="status"]'),note=form.querySelector('input[name="admin_note"]'),state=form.querySelector('[data-review-state]');
    let timer=null,sequence=0,controller=null;
    const save=async()=>{
        if(status.value==='needs_revision'&&!note.value.trim()){state.textContent='Catatan wajib diisi untuk status Perlu Perbaikan.';state.style.color='var(--ctl-danger)';note.setAttribute('aria-invalid','true');return;}
        note.removeAttribute('aria-invalid');const current=++sequence;controller?.abort();controller=new AbortController();state.textContent='Menyimpan…';state.style.color='var(--ctl-warn)';
        try{const response=await fetch(form.action,{method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':form.querySelector('input[name="_token"]').value},body:new FormData(form),signal:controller.signal});const body=await response.json().catch(()=>({}));if(current!==sequence)return;if(!response.ok)throw new Error(body.message||Object.values(body.errors||{}).flat()[0]||'Perubahan belum tersimpan.');state.textContent='Tersimpan';state.style.color='var(--ctl-success)';}
        catch(error){if(error.name==='AbortError')return;state.textContent=error.message||'Perubahan belum tersimpan.';state.style.color='var(--ctl-danger)';}
    };
    status?.addEventListener('change',save);
    note?.addEventListener('input',()=>{clearTimeout(timer);state.textContent='Menunggu perubahan…';state.style.color='var(--ctl-faint)';timer=setTimeout(save,650);});
});
</script>
@endpush
</x-admin.layouts.app>
