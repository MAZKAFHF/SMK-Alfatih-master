<x-admin.layouts.app :title="'Tambah Galeri'">
    <div class="mb-5"><a href="{{ route('admin.galleries.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Tambah Foto</h1></div>
    <x-ui.card class="p-6">
        <form method="POST" action="{{ route('admin.galleries.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <x-ui.input label="Judul" name="title" value="{{ old('title') }}" required />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Kategori" name="category" value="{{ old('category') }}" placeholder="Misal: Kegiatan, Fasilitas" list="cat-list" />
                <datalist id="cat-list">@foreach($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
                <x-ui.select label="Status" name="status" :value="old('status','published')" :options="['published'=>'Published','draft'=>'Draft','archived'=>'Archived']" required />
                <x-ui.input label="Urutan" name="order" type="number" value="{{ old('order',0) }}" />
            </div>
            <x-ui.image-preview label="Gambar" name="image" required help="Pilih foto kegiatan. Otomatis dioptimasi max 1600px." maxSize="Max 6MB, JPG/PNG/WEBP" />
            <div class="flex justify-end gap-3">
                <x-ui.button variant="ghost" href="{{ route('admin.galleries.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
