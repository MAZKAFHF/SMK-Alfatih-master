<x-admin.layouts.app :title="'Pengaturan'">
    <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Pengaturan Website</h1>
    <p class="mt-1 text-sm text-slate-500">Atur informasi sekolah, homepage, konten halaman, dan SEO. PPDB dikelola melalui Periode PPDB.</p>

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
        </div>

        <div id="tab-general" data-tab-panel>
            <x-ui.card class="p-6">
                <form method="POST" novalidate action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5">
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
                        <x-ui.file-upload label="Logo" name="logo" accept="PNG/JPG/WEBP" max-note="Maks. 2 MB" button-text="Pilih Logo" />
                        <x-ui.file-upload label="Favicon" name="favicon" accept="PNG/ICO/JPG" max-note="Maks. 1 MB" button-text="Pilih Favicon" />
                    </div>
                    <div class="flex justify-end"><x-ui.button type="submit">Simpan Umum</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>

        <div id="tab-homepage" data-tab-panel class="hidden">
            <x-ui.card class="p-6">
                <form method="POST" novalidate action="{{ route('admin.settings.update') }}" class="space-y-5">
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
                <form method="POST" novalidate action="{{ route('admin.settings.update') }}" class="space-y-5">
                    @csrf @method('PUT')
                    <x-ui.input label="SEO Default Title" name="seo_title" value="{{ old('seo_title', \App\Models\SiteSetting::get('seo_title')) }}" />
                    <x-ui.textarea label="SEO Default Description" name="seo_description" rows="3">{{ old('seo_description', \App\Models\SiteSetting::get('seo_description')) }}</x-ui.textarea>
                    <div class="rounded-xl p-4" style="background: var(--ctl-sunken); border: 1px solid var(--ctl-border);">
                        <h3 class="text-sm font-bold" style="color: var(--ctl-text);">MEDIA SOSIAL</h3>
                        <p class="mt-1 text-xs" style="color: var(--ctl-muted);">Tautan resmi yang tampil di footer &amp; halaman kontak. Kosongkan untuk menyembunyikan platform. Perubahan langsung tampil di website tanpa cache:clear.</p>
                        <div class="mt-4 grid gap-5 sm:grid-cols-2">
                            <x-ui.input label="Instagram" name="social_instagram" value="{{ old('social_instagram', \App\Models\SiteSetting::get('social_instagram', 'https://www.instagram.com/smktahfizhalfatihpku/')) }}" placeholder="https://www.instagram.com/smktahfizhalfatihpku/" help="Wajib URL instagram.com. Kosongkan untuk sembunyikan." />
                            <x-ui.input label="Facebook" name="social_facebook" value="{{ old('social_facebook', \App\Models\SiteSetting::get('social_facebook', 'https://www.facebook.com/people/SmkTahfizh-AlFatih')) }}" placeholder="https://www.facebook.com/people/SmkTahfizh-AlFatih" help="Wajib URL facebook.com. Kosongkan untuk sembunyikan." />
                            <x-ui.input label="YouTube" name="social_youtube" value="{{ old('social_youtube', \App\Models\SiteSetting::get('social_youtube', 'https://www.youtube.com/@SMKTAHFIZHALFATIH')) }}" placeholder="https://www.youtube.com/@SMKTAHFIZHALFATIH" help="Wajib URL youtube.com. Kosongkan untuk sembunyikan." />
                            <x-ui.input label="TikTok" name="social_tiktok" value="{{ old('social_tiktok', \App\Models\SiteSetting::get('social_tiktok')) }}" placeholder="https://tiktok.com/@..." help="Opsional. Kosongkan untuk sembunyikan." />
                        </div>
                    </div>
                    <div class="flex justify-end"><x-ui.button type="submit">Simpan SEO &amp; Sosial</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>

        <div id="tab-pages" data-tab-panel class="hidden">
            <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Edit konten halaman statis yang tampil di website. Perubahan akan langsung aktif di website setelah disimpan.</p>

            <div data-tabs class="mb-6">
                <div class="flex gap-1.5 overflow-x-auto border-b border-slate-200 dark:border-slate-700 pb-2">
                    @foreach($pageLabels as $slug => $label)
                        <button type="button" data-tab-trigger data-target="#page-{{ $slug }}" data-active="{{ $loop->first ? 'true' : 'false' }}" class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold border data-[active=true]:bg-primary-600 data-[active=true]:text-white data-[active=true]:border-primary-600 data-[active=false]:bg-white data-[active=false]:text-slate-600 data-[active=false]:border-slate-200 dark:data-[active=false]:bg-slate-800 dark:data-[active=false]:text-slate-300">
                            {{ $label }}
                        </button>
                    @endforeach
                    <button type="button" data-tab-trigger data-target="#page-custom" data-active="false" class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-xs font-semibold border data-[active=true]:bg-slate-800 data-[active=true]:text-white data-[active=false]:bg-white data-[active=false]:text-slate-600 data-[active=false]:border-slate-200">Lainnya</button>
                </div>

                @foreach($pageLabels as $slug => $label)
                    @php $page = $pages[$slug] ?? null; @endphp
                    <div id="page-{{ $slug }}" data-tab-panel @if(!$loop->first) class="hidden" @endif>
                        @if($page)
                            <x-ui.card class="p-6">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit: {{ $label }}</h3>
                                <p class="mt-1 text-xs text-slate-500">URL: <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="text-primary-600 hover:underline">/{{ $page->slug }}</a> • Status: {{ $page->status->label() }}</p>
                                <form method="POST" novalidate action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="mt-4 space-y-5">
                                    @csrf @method('PUT')
                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <x-ui.input label="Judul" name="title" value="{{ old('title', $page->title) }}" required />
                                        <x-ui.input label="Slug" name="slug" value="{{ old('slug', $page->slug) }}" required help="Huruf kecil, tanpa spasi. Ubah akan mengubah URL." />
                                    </div>
                                    <x-ui.rich-text-editor label="Konten" name="content" :value="old('content', $page->content)" required />
                                    <x-ui.image-preview label="Gambar" name="image" :value="$page->image" help="Biarkan kosong untuk memakai gambar saat ini." maxSize="Max 4MB, JPG/PNG/WEBP" />
                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <x-ui.input label="Meta Title" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" placeholder="Kosongkan = pakai judul" />
                                        <x-ui.input label="Urutan Navbar" name="order" type="number" value="{{ old('order', $page->order) }}" />
                                    </div>
                                    <x-ui.textarea label="Meta Description" name="meta_description" rows="2" placeholder="Deskripsi SEO (max 500)">{{ old('meta_description', $page->meta_description) }}</x-ui.textarea>
                                    <x-ui.select label="Status" name="status" :value="old('status', $page->status->value)" :options="['published'=>'Published','draft'=>'Draft','archived'=>'Archived']" required help="Draft tidak tampil di website" />
                                    <div class="flex justify-end gap-3 pt-2">
                                        <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:underline">Lihat Halaman</a>
                                        <x-ui.button type="submit">Simpan {{ $label }}</x-ui.button>
                                    </div>
                                </form>
                            </x-ui.card>
                        @else
                            <x-ui.card class="p-6"><p class="text-sm text-slate-500">Halaman {{ $label }} belum ada. <a href="{{ route('admin.pages.create') }}" class="text-primary-600 hover:underline">Buat baru</a></p></x-ui.card>
                        @endif
                    </div>
                @endforeach

                <div id="page-custom" data-tab-panel class="hidden">
                    <x-ui.card class="p-6">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Halaman Lainnya</h3>
                        <p class="mt-1 text-xs text-slate-500">Halaman custom di luar 5 utama. Kelola di <a href="{{ route('admin.pages.index') }}" class="text-primary-600 hover:underline">Admin &gt; Halaman</a>.</p>
                        @if($customPages->isEmpty())
                            <p class="mt-4 text-sm text-slate-400">Belum ada halaman custom.</p>
                        @else
                            <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach($customPages as $cp)
                                    <div class="flex items-center justify-between gap-3 py-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $cp->title }}</p>
                                            <p class="truncate text-xs text-slate-500">/{{ $cp->slug }} • {{ $cp->status->label() }}</p>
                                        </div>
                                        <div class="flex shrink-0 gap-1.5">
                                            <x-ui.button size="xs" variant="ghost" href="{{ route('pages.show', $cp->slug) }}" target="_blank">Lihat</x-ui.button>
                                            <x-ui.button size="xs" variant="outline" href="{{ route('admin.pages.edit', $cp) }}">Edit</x-ui.button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-4"><x-ui.button size="sm" href="{{ route('admin.pages.create') }}">+ Tambah Halaman Baru</x-ui.button></div>
                    </x-ui.card>
                </div>
            </div>
        </div>

    </div>
</x-admin.layouts.app>
