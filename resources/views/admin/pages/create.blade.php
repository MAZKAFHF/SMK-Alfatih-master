<x-admin.layouts.app :title="'Tambah Halaman'">
    <div class="mb-5"><a href="{{ route('admin.pages.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Tambah Halaman</h1></div>
    <x-ui.card class="p-6">
        <form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Judul" name="title" value="{{ old('title') }}" required />
                <x-ui.input label="Slug" name="slug" value="{{ old('slug') }}" placeholder="Otomatis" />
            </div>
            <x-ui.rich-text-editor label="Konten" name="content" :value="old('content')" help="Konten halaman akan ditampilkan di halaman publik dengan format yang sama." required />
            <x-ui.image-preview label="Gambar Header" name="image" help="Gambar header halaman, tampil di halaman publik." maxSize="Max 4MB, JPG/PNG/WEBP" />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.select label="Status" name="status" :value="old('status','published')" :options="['published'=>'Published','draft'=>'Draft','archived'=>'Archived']" required />
                <x-ui.input label="Urutan (navbar)" name="order" type="number" value="{{ old('order',0) }}" />
                <x-ui.input label="Meta Title" name="meta_title" value="{{ old('meta_title') }}" />
            </div>
            <x-ui.input label="Meta Description" name="meta_description" value="{{ old('meta_description') }}" />
            <div class="flex justify-end gap-3">
                <x-ui.button variant="ghost" href="{{ route('admin.pages.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
