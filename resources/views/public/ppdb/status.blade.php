<x-layouts.app :title="'Cek Status PPDB'">
    <x-page-header
        title="Cek Status Pendaftaran"
        subtitle="Pantau status pendaftaran Anda menggunakan nomor pendaftaran."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'PPDB', 'url' => route('ppdb.index')],
            ['label' => 'Cek Status'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <x-ui.card>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Masukkan Nomor Pendaftaran</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Masukkan nomor pendaftaran yang Anda terima setelah mengirim formulir.
                </p>

                <form method="GET" action="{{ route('ppdb.status') }}" class="mt-6" novalidate>
                    <x-ui.input
                        label="Nomor Pendaftaran"
                        name="registration_number"
                        value="{{ $registrationNumber }}"
                        placeholder="Contoh: PPDB-2026-00001"
                        required
                        autofocus
                    />
                    <div class="mt-4">
                        <x-ui.input
                            label="Tanggal Lahir (untuk verifikasi)"
                            name="birth_date"
                            type="date"
                            value="{{ $birthDate ?? '' }}"
                            required
                        />
                        <p class="mt-1 text-xs text-slate-400">Tanggal lahir harus cocok dengan data pendaftaran.</p>
                    </div>

                    <div class="mt-6">
                        <x-ui.button type="submit" size="lg" full="true">Cek Status</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            @if (session('success'))
                <div class="mt-6">
                    <x-ui.alert variant="success" title="Pendaftaran berhasil dikirim" dismissible>
                        {{ session('success') }}
                    </x-ui.alert>
                </div>
            @endif

            @if(isset($error))
                <div class="mt-6"><x-ui.alert variant="danger" title="Terlalu banyak percobaan" dismissible>{{ $error }}</x-ui.alert></div>
            @endif

            @if ($registrationNumber !== '')
                @if ($registration && empty($birthDate) && $registrationNumber !== '')
                    <div class="mt-6"><x-ui.alert variant="warning" title="Verifikasi diperlukan" >Untuk privasi, masukkan <strong>tanggal lahir</strong> sesuai data pendaftaran untuk melihat status lengkap.</x-ui.alert></div>
                @endif
                @if ($registration)
                    <x-ui.card class="mt-6 p-6">
                        <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 dark:border-slate-700/50 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Nomor Pendaftaran</p>
                                <p class="mt-1 font-mono text-base font-bold text-slate-900 dark:text-white">{{ $registration->registration_number }}</p>
                            </div>
                            <div class="flex flex-col items-start gap-1 sm:items-end">
                                <x-ui.badge :color="$registration->status->badgeColor()" size="lg" dot>{{ $registration->status->label() }}</x-ui.badge>
                                <span class="text-xs text-slate-400 dark:text-slate-500">Terdaftar {{ $registration->created_at->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>

                        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Nama Lengkap</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $registration->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Program Keahlian</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $registration->program?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Asal Sekolah</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $registration->school_origin ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Lahir</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ $registration->birth_date?->translatedFormat('d M Y') ?? '—' }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6 rounded-lg bg-slate-50 p-4 dark:bg-slate-800/50">
                            <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                @if ($registration->status->value === 'accepted')
                                    Selamat! Anda <strong class="font-semibold text-emerald-700">diterima</strong> di SMK Tahfizh Al-Fatih. Informasi selanjutnya akan disampaikan oleh panitia melalui kontak yang terdaftar.
                                @elseif ($registration->status->value === 'rejected')
                                    Mohon maaf, pendaftaran Anda belum dapat diterima. Silakan hubungi panitia untuk informasi lebih lanjut.
                                @elseif ($registration->status->value === 'cancelled')
                                    Pendaftaran Anda telah dibatalkan. Silakan hubungi panitia apabila ini merupakan kekeliruan.
                                @else
                                    Pendaftaran Anda sedang dalam proses verifikasi oleh panitia. Silakan pantau halaman ini secara berkala.
                                @endif
                            </p>
                        </div>
                    </x-ui.card>
                @else
                    <div class="mt-6">
                        <x-ui.alert variant="danger" title="Data tidak ditemukan" dismissible>
                            Tidak ada pendaftaran dengan nomor <strong class="font-mono">{{ $registrationNumber }}</strong>. Periksa kembali nomor pendaftaran Anda.
                        </x-ui.alert>
                    </div>
                @endif
            @else
                <div class="mt-6">
                    <x-ui.empty-state
                        icon="false"
                        title="Masukkan nomor pendaftaran"
                        description="Masukkan nomor pendaftaran pada formulir di atas untuk melihat status pendaftaran Anda."
                    >
                        <x-slot:action>
                            <x-ui.button variant="outline" size="sm" href="{{ route('ppdb.index') }}">Daftar PPDB</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
