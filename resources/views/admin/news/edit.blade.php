<x-admin.layouts.app :title="'Edit Berita'">
    <div class="mb-5"><a href="{{ route('admin.news.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Kembali</a>
        <h1 class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">Edit: {{ $news->title }}</h1></div>
    <x-ui.card class="p-6">
        <form method="POST" action="{{ route('admin.news.update',$news) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Judul" name="title" value="{{ old('title',$news->title) }}" required />
                <x-ui.input label="Slug" name="slug" value="{{ old('slug',$news->slug) }}" required />
            </div>
            <x-ui.rich-text-editor label="Konten" name="content" :value="old('content', $news->content)" required />
            <div class="sm:col-span-2">
                <x-ui.image-preview label="Thumbnail" name="thumbnail" :value="$news->thumbnail" help="Biarkan kosong untuk memakai thumbnail saat ini." maxSize="Max 4MB, JPG/PNG/WEBP" />
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.select label="Status" name="status" :value="old('status',$news->status->value)" :options="['draft'=>'Draft','published'=>'Published','archived'=>'Archived']" required />
                <x-ui.input label="Jadwal Publikasi" name="published_at" type="datetime-local" value="{{ old('published_at', $news->published_at?->timezone('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" href="{{ route('admin.news.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
