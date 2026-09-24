<x-admin.layouts.app :title="'Tambah Program'">
    <div class="mb-5"><a href="{{ route('admin.programs.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400">&larr; Kembali</a>
        <h1 class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">Tambah Program Keahlian</h1></div>

    <x-ui.card class="p-6">
        <form method="POST" action="{{ route('admin.programs.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Nama Program" name="name" value="{{ old('name') }}" placeholder="Contoh: PPLG" required />
                <x-ui.input label="Slug" name="slug" value="{{ old('slug') }}" placeholder="Otomatis dari nama jika kosong" />
            </div>
            <x-ui.input label="Deskripsi Singkat" name="short_description" value="{{ old('short_description') }}" placeholder="Untuk card homepage" required />
            <x-ui.rich-text-editor label="Deskripsi Lengkap" name="description" :value="old('description')" help="Deskripsi akan ditampilkan di halaman detail program dengan format." required />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Gambar" name="image" type="file" accept="image/*" />
                <x-ui.select label="Status" name="status" :value="old('status','active')" :options="['active'=>'Aktif','inactive'=>'Nonaktif']" required />
                <x-ui.input label="Urutan" name="order" type="number" value="{{ old('order',0) }}" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" href="{{ route('admin.programs.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
