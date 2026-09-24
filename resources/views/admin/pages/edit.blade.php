<x-admin.layouts.app :title="'Edit Halaman'">
    <div class="mb-5"><a href="{{ route('admin.pages.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Edit: {{ $page->title }}</h1></div>
    <x-ui.card class="p-6">
        @if($page->image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->getRawOriginal('image')) }}" class="mb-4 h-40 rounded-lg border object-cover" alt="" />@endif
        <form method="POST" action="{{ route('admin.pages.update',$page) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Judul" name="title" value="{{ old('title',$page->title) }}" required />
                <x-ui.input label="Slug" name="slug" value="{{ old('slug',$page->slug) }}" required />
            </div>
            <x-ui.rich-text-editor label="Konten" name="content" :value="old('content', $page->content)" required />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Ganti Gambar" name="image" type="file" accept="image/*" />
                <x-ui.select label="Status" name="status" :value="old('status',$page->status->value)" :options="['published'=>'Published','draft'=>'Draft','archived'=>'Archived']" required />
                <x-ui.input label="Urutan" name="order" type="number" value="{{ old('order',$page->order) }}" />
                <x-ui.input label="Meta Title" name="meta_title" value="{{ old('meta_title',$page->meta_title) }}" />
            </div>
            <x-ui.textarea label="Meta Description" name="meta_description" rows="2">{{ old('meta_description',$page->meta_description) }}</x-ui.textarea>
            <div class="flex justify-end gap-3">
                <x-ui.button variant="ghost" href="{{ route('admin.pages.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
