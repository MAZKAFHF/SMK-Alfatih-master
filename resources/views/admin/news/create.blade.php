<x-admin.layouts.app :title="'Tambah Berita'">
    <div class="mb-5"><a href="{{ route('admin.news.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Kembali</a>
        <h1 class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">Tambah Berita</h1></div>
    <x-ui.card class="p-6">
        <form method="POST" novalidate action="{{ route('admin.news.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <x-ui.form-section title="Informasi Berita" description="Judul, slug, dan konten yang akan tampil di halaman publik.">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input label="Judul" name="title" value="{{ old('title') }}" required />
                    <x-ui.input label="Slug" name="slug" value="{{ old('slug') }}" placeholder="Otomatis dari judul" help="Huruf kecil, tanpa spasi, unik." />
                </div>
                <x-ui.rich-text-editor label="Konten" name="content" :value="old('content')" help="Gunakan heading, list, tautan. Konten akan dibersihkan otomatis dari script berbahaya." required />
            </x-ui.form-section>

            <x-ui.form-section title="Media">
                <x-ui.image-preview label="Thumbnail" name="thumbnail" help="JPG/PNG/WEBP, max 4MB" />
            </x-ui.form-section>

            <x-ui.publish-panel status="{{ old('status','draft') }}">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.select label="Status" name="status" :value="old('status','draft')" :options="['draft'=>'Draft','published'=>'Diterbitkan','archived'=>'Diarsipkan']" required />
                    <x-ui.datetime-picker label="Jadwal Publikasi" name="published_at" value="{{ old('published_at') }}" help="Kosongkan untuk terbit sekarang." />
                </div>
            </x-ui.publish-panel>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" href="{{ route('admin.news.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
