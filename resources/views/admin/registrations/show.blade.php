<x-admin.layouts.app :title="'Detail Pendaftar PPDB'">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.registrations.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">
                &larr; Kembali ke daftar
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $registration->name }}</h2>
                <x-ui.badge :color="$registration->status->badgeColor()" dot>{{ $registration->status->label() }}</x-ui.badge>
            </div>
            <p class="mt-1 font-mono text-sm text-slate-500 dark:text-slate-400">{{ $registration->registration_number }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card class="p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Data Calon Siswa</h3>
                <dl class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">NISN</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $registration->nisn ?? '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Jenis Kelamin</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white capitalize">{{ $registration->gender ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Tempat, Tanggal Lahir</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">
                            {{ $registration->birth_place ? $registration->birth_place.', ' : '' }}{{ $registration->birth_date?->translatedFormat('d M Y') ?? '—' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Asal Sekolah</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $registration->school_origin ?? '—' }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Alamat</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $registration->address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">No. HP / WhatsApp</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $registration->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Email</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $registration->email ?? '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card class="p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Program Keahlian</h3>
                <p class="mt-2 text-sm font-medium text-slate-900 dark:text-white">{{ $registration->program?->name ?? 'Tidak memilih program' }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Terdaftar pada {{ $registration->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
            </x-ui.card>

            <x-ui.card class="p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Perbarui Status</h3>
                <form method="POST" action="{{ route('admin.registrations.update', $registration) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    @php
                        $statusOptions = collect(App\Enums\RegistrationStatus::cases())
                            ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
                            ->all();
                    @endphp

                    <x-ui.select
                        name="status"
                        label="Status Pendaftaran"
                        :value="$registration->status->value"
                        :options="$statusOptions"
                        :placeholder-option="false"
                        required
                    />

                    <x-ui.button type="submit" size="md" full="true">Simpan Status</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card class="border-red-200 bg-red-50/40 p-6 dark:border-red-900 dark:bg-red-950/30">
                <h3 class="text-sm font-bold text-red-800 dark:text-red-400">Hapus Pendaftaran</h3>
                <p class="mt-1 text-xs leading-relaxed text-red-700 dark:text-red-300">Menghapus data ini bersifat permanen dan tidak dapat dibatalkan.</p>
                <x-ui.button
                    variant="danger"
                    size="sm"
                    class="mt-4"
                    data-confirm-title="Hapus pendaftaran ini?"
                    data-confirm-message="Data pendaftaran {{ $registration->name }} ({{ $registration->registration_number }}) akan dihapus permanen."
                    onclick="confirmDialog({ title: this.dataset.confirmTitle, message: this.dataset.confirmMessage, formAction: '{{ route('admin.registrations.destroy', $registration) }}', method: 'DELETE' })"
                >Hapus</x-ui.button>
            </x-ui.card>
        </div>
    </div>
</x-admin.layouts.app>
