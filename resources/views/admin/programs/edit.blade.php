<x-admin.layouts.app :title="'Edit Program'">
    <div class="mb-5"><a href="{{ route('admin.programs.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400">&larr; Kembali</a>
        <h1 class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">Edit: {{ $program->name }}</h1></div>

    <x-ui.card class="p-6">
        <form method="POST" novalidate action="{{ route('admin.programs.update',$program) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="Nama Program" name="name" value="{{ old('name',$program->name) }}" required />
                <x-ui.input label="Slug" name="slug" value="{{ old('slug',$program->slug) }}" required />
            </div>
            <x-ui.input label="Deskripsi Singkat" name="short_description" value="{{ old('short_description',$program->short_description) }}" required />
            <x-ui.rich-text-editor label="Deskripsi Lengkap" name="description" :value="old('description', $program->description)" required />
            <div>
                <x-ui.image-preview label="Gambar" name="image" :value="$program->image" help="Biarkan kosong untuk memakai gambar saat ini." maxSize="Max 4MB, JPG/PNG/WEBP" />
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <x-ui.select label="Status" name="status" :value="old('status',$program->status->value)" :options="['active'=>'Aktif','inactive'=>'Nonaktif']" required />
                    <x-ui.input label="Urutan" name="order" type="number" value="{{ old('order',$program->order) }}" />
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" href="{{ route('admin.programs.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
