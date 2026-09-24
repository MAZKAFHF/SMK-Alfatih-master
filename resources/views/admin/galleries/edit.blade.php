<x-admin.layouts.app :title="'Edit Galeri'">
    <div class="mb-5"><a href="{{ route('admin.galleries.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Edit: {{ $gallery->title }}</h1></div>
    <x-ui.card class="p-6">
        @if($gallery->image)<img src="{{ $gallery->image }}" class="mb-4 h-48 w-auto rounded-lg border object-cover" alt="" />@endif
        <form method="POST" action="{{ route('admin.galleries.update',$gallery) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <x-ui.input label="Judul" name="title" value="{{ old('title',$gallery->title) }}" required />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Kategori" name="category" value="{{ old('category',$gallery->category) }}" />
                <x-ui.select label="Status" name="status" :value="old('status',$gallery->status->value)" :options="['published'=>'Published','draft'=>'Draft','archived'=>'Archived']" required />
                <x-ui.input label="Urutan" name="order" type="number" value="{{ old('order',$gallery->order) }}" />
            </div>
            <x-ui.input label="Ganti Gambar" name="image" type="file" accept="image/*" />
            <div class="flex justify-end gap-3">
                <x-ui.button variant="ghost" href="{{ route('admin.galleries.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
