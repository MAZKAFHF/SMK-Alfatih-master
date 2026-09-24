<x-layouts.app :title="'Daftar PPDB'">
    <x-page-header
        title="Daftar PPDB"
        subtitle="Formulir Penerimaan Peserta Didik Baru SMK Tahfizh Al-Fatih Tahun Ajaran 2026/2027"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'PPDB', 'url' => route('ppdb.index')],
            ['label' => 'Daftar Siswa'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-section-heading align="center" subtitle="Formulir Pendaftaran Siswa" title="Isi Data Calon Siswa">
                Tandai (<span class="font-semibold text-red-500">*</span>) wajib diisi. Pastikan data yang Anda masukkan benar dan dapat dihubungi.
            </x-section-heading>

            @if ($errors->any())
                <div class="mt-8">
                    <x-ui.alert variant="danger" title="Formulir belum lengkap">
                        Ada beberapa data yang perlu diperbaiki. Silakan periksa kembali isian Anda di bawah ini.
                    </x-ui.alert>
                </div>
            @endif

            <form method="POST" action="{{ route('ppdb.store') }}" class="mt-8 space-y-6" novalidate>
                @csrf

                <x-ui.card class="p-6">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Data Calon Siswa</h2>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-ui.input
                                label="Nama Lengkap"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Sesuai akta kelahiran"
                                required
                            />
                        </div>

                        <x-ui.input
                            label="NISN"
                            name="nisn"
                            value="{{ old('nisn') }}"
                            placeholder="Nomor Induk Siswa Nasional"
                        />

                        <x-ui.select
                            label="Jenis Kelamin"
                            name="gender"
                            :value="old('gender')"
                            :options="['laki-laki' => 'Laki-laki', 'perempuan' => 'Perempuan']"
                            placeholder="Pilih jenis kelamin"
                            required
                        />

                        <x-ui.input
                            label="Tempat Lahir"
                            name="birth_place"
                            value="{{ old('birth_place') }}"
                        />

                        <x-ui.input
                            label="Tanggal Lahir"
                            name="birth_date"
                            type="date"
                            value="{{ old('birth_date') }}"
                            max="{{ now()->subYears(12)->toDateString() }}"
                        />

                        <div class="sm:col-span-2">
                            <x-ui.input
                                label="Asal Sekolah"
                                name="school_origin"
                                value="{{ old('school_origin') }}"
                                placeholder="Contoh: SMPN 1 Kota Bogor"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <x-ui.textarea
                                label="Alamat"
                                name="address"
                                rows="3"
                                placeholder="Alamat lengkap tempat tinggal"
                            >{{ old('address') }}</x-ui.textarea>
                        </div>

                        <x-ui.input
                            label="No. HP / WhatsApp"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="Contoh: 081234567890"
                            type="tel"
                        />

                        <x-ui.input
                            label="Email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            type="email"
                        />
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Data Orang Tua / Wali</h2>
                    <div class="mt-5 grid gap-5">
                        <x-ui.input
                            label="Nama Orang Tua / Wali"
                            name="parent_name"
                            value="{{ old('parent_name') }}"
                            placeholder="Nama lengkap orang tua atau wali"
                        />
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Pilihan Program Keahlian</h2>
                    <div class="mt-5">
                        <x-ui.select
                            label="Program Keahlian"
                            name="program_id"
                            :value="old('program_id')"
                            :options="$programs->pluck('name', 'id')->all()"
                            placeholder="Pilih program keahlian"
                            required
                        />
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Dengan mengirim formulir, Anda menyatakan data yang diisi adalah benar.
                        </p>
                        <x-ui.button type="submit" size="lg">Kirim Pendaftaran</x-ui.button>
                    </div>
                </x-ui.card>
            </form>
        </div>
    </section>
</x-layouts.app>
