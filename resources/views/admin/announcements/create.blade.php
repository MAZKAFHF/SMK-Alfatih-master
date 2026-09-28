<x-admin.layouts.app :title="'Tambah Pengumuman'">
    <div class="mb-5"><a href="{{ route('admin.announcements.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Tambah Pengumuman</h1></div>
    <x-ui.card class="p-6">
        <form method="POST" novalidate action="{{ route('admin.announcements.store') }}" class="space-y-6">
            @csrf
            <x-ui.form-section title="Informasi Pengumuman" description="Judul dan konten yang akan tampil di halaman publik.">
                <x-ui.input label="Judul" name="title" value="{{ old('title') }}" required />
                <x-ui.rich-text-editor label="Konten" name="content" :value="old('content')" help="Gunakan heading, list, tautan, kutipan. Konten akan ditampilkan dengan format di halaman publik." required />
            </x-ui.form-section>

            <x-ui.publish-panel status="{{ old('status','draft') }}">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.select label="Status" name="status" :value="old('status','draft')" :options="['draft'=>'Draft','published'=>'Diterbitkan','archived'=>'Diarsipkan']" required />
                    <x-ui.datetime-picker label="Jadwal Publikasi" name="published_at" value="{{ old('published_at') }}" help="Kosongkan untuk terbit sekarang jika status Diterbitkan." />
                </div>
            </x-ui.publish-panel>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" href="{{ route('admin.announcements.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
