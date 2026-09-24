<x-admin.layouts.app :title="'Pengaturan'">
    <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Pengaturan Website</h1>
    <p class="mt-1 text-sm text-slate-500">Atur informasi sekolah, homepage, SEO &amp; PPDB.</p>

    @php
        $pageLabels = [
            'profil' => 'Profil',
            'sejarah' => 'Sejarah',
            'visi-misi' => 'Visi & Misi',
            'sambutan-kepala-sekolah' => 'Sambutan',
            'fasilitas' => 'Fasilitas',
        ];
    @endphp
    <div class="mt-6" data-tabs>
        <div class="flex gap-2 border-b border-slate-200 dark:border-slate-700 mb-6 overflow-x-auto">
            <button type="button" data-tab-trigger data-target="#tab-general" data-active="true" class="whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 data-[active=true]:border-primary-600 data-[active=true]:text-primary-700 data-[active=false]:border-transparent data-[active=false]:text-slate-500">Umum &amp; Kontak</button>
            <button type="button" data-tab-trigger data-target="#tab-homepage" data-active="false" class="whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 data-[active=true]:border-primary-600 data-[active=false]:border-transparent data-[active=false]:text-slate-500">Homepage</button>
            <button type="button" data-tab-trigger data-target="#tab-pages" data-active="false" class="whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 data-[active=true]:border-primary-600 data-[active=false]:border-transparent data-[active=false]:text-slate-500">Konten Halaman</button>
            <button type="button" data-tab-trigger data-target="#tab-seo" data-active="false" class="whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 data-[active=true]:border-primary-600 data-[active=false]:border-transparent data-[active=false]:text-slate-500">SEO &amp; Sosial</button>
            <button type="button" data-tab-trigger data-target="#tab-ppdb" data-active="false" class="whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 data-[active=true]:border-primary-600 data-[active=false]:border-transparent data-[active=false]:text-slate-500">PPDB</button>
        </div>

        <div id="tab-general" data-tab-panel>
            <x-ui.card class="p-6">
                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf @method('PUT')
                    @php $settings = $settings ?? collect(); @endphp
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Nama Sekolah" name="school_name" value="{{ old('school_name', \App\Models\SiteSetting::get('school_name', config('app.name'))) }}" />
                        <x-ui.input label="Tagline" name="school_tagline" value="{{ old('school_tagline', \App\Models\SiteSetting::get('school_tagline')) }}" />
                        <x-ui.input label="Email" name="school_email" type="email" value="{{ old('school_email', \App\Models\SiteSetting::get('school_email','info@smkalfatih.sch.id')) }}" />
                        <x-ui.input label="Telepon" name="school_phone" value="{{ old('school_phone', \App\Models\SiteSetting::get('school_phone','(021) 1234-5678')) }}" />
                        <x-ui.input label="WhatsApp" name="school_whatsapp" value="{{ old('school_whatsapp', \App\Models\SiteSetting::get('school_whatsapp')) }}" />
                        <x-ui.input label="Nama Kepala Sekolah" name="headmaster_name" value="{{ old('headmaster_name', \App\Models\SiteSetting::get('headmaster_name')) }}" />
                        <x-ui.input label="Tahun Berdiri" name="founding_year" type="number" value="{{ old('founding_year', \App\Models\SiteSetting::get('founding_year','2016')) }}" />
                        <x-ui.input label="Alamat" name="school_address" value="{{ old('school_address', \App\Models\SiteSetting::get('school_address','Jl. Pendidikan No. 1, Jakarta')) }}" class="sm:col-span-2" />
                        <x-ui.input label="Google Maps URL" name="maps_url" value="{{ old('maps_url', \App\Models\SiteSetting::get('maps_url')) }}" placeholder="https://maps.google.com/..." class="sm:col-span-2" />
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Logo (png/jpg/webp)" name="logo" type="file" accept="image/*" />
                        <x-ui.input label="Favicon" name="favicon" type="file" accept="image/*" />
                    </div>
                    <div class="flex justify-end"><x-ui.button type="submit">Simpan Umum</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>

        <div id="tab-homepage" data-tab-panel class="hidden">
            <x-ui.card class="p-6">
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                    @csrf @method('PUT')
                    <p class="text-sm text-slate-500">Angka statistik di homepage (kosongkan untuk sembunyikan). Verifikasi kebenaran data sebelum menyimpan.</p>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Program Keahlian" name="stat_programs" value="{{ old('stat_programs', \App\Models\SiteSetting::get('stat_programs','4')) }}" />
                        <x-ui.input label="Tahun Berdiri" name="stat_founded" value="{{ old('stat_founded', \App\Models\SiteSetting::get('stat_founded','2016')) }}" />
                        <x-ui.input label="Siswa Aktif" name="stat_students" value="{{ old('stat_students', \App\Models\SiteSetting::get('stat_students','850+')) }}" />
                        <x-ui.input label="Alumni" name="stat_alumni" value="{{ old('stat_alumni', \App\Models\SiteSetting::get('stat_alumni','1200+')) }}" />
                    </div>
                    <div class="flex justify-end"><x-ui.button type="submit">Simpan Homepage</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>

        <div id="tab-seo" data-tab-panel class="hidden">
            <x-ui.card class="p-6">
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                    @csrf @method('PUT')
                    <x-ui.input label="SEO Default Title" name="seo_title" value="{{ old('seo_title', \App\Models\SiteSetting::get('seo_title')) }}" />
                    <x-ui.textarea label="SEO Default Description" name="seo_description" rows="3">{{ old('seo_description', \App\Models\SiteSetting::get('seo_description')) }}</x-ui.textarea>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Instagram" name="social_instagram" value="{{ old('social_instagram', \App\Models\SiteSetting::get('social_instagram')) }}" placeholder="https://instagram.com/..." />
                        <x-ui.input label="YouTube" name="social_youtube" value="{{ old('social_youtube', \App\Models\SiteSetting::get('social_youtube')) }}" />
                        <x-ui.input label="Facebook" name="social_facebook" value="{{ old('social_facebook', \App\Models\SiteSetting::get('social_facebook')) }}" />
                        <x-ui.input label="TikTok" name="social_tiktok" value="{{ old('social_tiktok', \App\Models\SiteSetting::get('social_tiktok')) }}" />
                    </div>
                    <div class="flex justify-end"><x-ui.button type="submit">Simpan SEO</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>

        <div id="tab-ppdb" data-tab-panel class="hidden">
            <x-ui.card class="p-6">
                <form method="POST" action="{{ route('admin.settings.ppdb') }}" class="space-y-5">
                    @csrf @method('PUT')
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Tahun Ajaran" name="academic_year" value="{{ old('academic_year',$ppdb->academic_year) }}" required />
                        <x-ui.input label="Kuota (opsional)" name="quota" type="number" value="{{ old('quota',$ppdb->quota) }}" />
                        <x-ui.input label="Dibuka Pada" name="opens_at" type="datetime-local" value="{{ old('opens_at',$ppdb->opens_at?->format('Y-m-d\TH:i')) }}" />
                        <x-ui.input label="Ditutup Pada" name="closes_at" type="datetime-local" value="{{ old('closes_at',$ppdb->closes_at?->format('Y-m-d\TH:i')) }}" />
                        <x-ui.select label="Status Override" name="status_override" :value="old('status_override',$ppdb->status_override)" :options="['open'=>'Paksa Buka','closed'=>'Paksa Tutup']" placeholder="Otomatis (berdasarkan tanggal)"><option value="">Otomatis</option></x-ui.select>
                        <div class="flex items-center gap-2 pt-6">
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_open" value="1" {{ old('is_open',$ppdb->is_open) ? 'checked' : '' }} class="rounded border-slate-300" /> Aktif</label>
                        </div>
                    </div>
                    <x-ui.textarea label="Pengumuman PPDB" name="announcement" rows="3">{{ old('announcement',$ppdb->announcement) }}</x-ui.textarea>
                    <x-ui.input label="Kontak Bantuan" name="contact_info" value="{{ old('contact_info',$ppdb->contact_info) }}" placeholder="WA Panitia: 0812..." />
                    <div class="flex justify-end"><x-ui.button type="submit">Simpan PPDB</x-ui.button></div>
                </form>
                <div class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-500 dark:bg-slate-800">Status saat ini: <strong class="text-slate-900 dark:text-white">{{ $ppdb->statusLabel() }}</strong> • {{ $ppdb->isOpen() ? 'Pendaftaran menerima POST' : 'POST ditolak server' }}</div>
            </x-ui.card>
        </div>
    </div>
</x-admin.layouts.app>
