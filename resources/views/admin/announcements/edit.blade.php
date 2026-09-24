<x-admin.layouts.app :title="'Edit Pengumuman'">
    <div class="mb-5"><a href="{{ route('admin.announcements.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Edit: {{ $announcement->title }}</h1></div>
    <x-ui.card class="p-6">
        <form method="POST" action="{{ route('admin.announcements.update',$announcement) }}" class="space-y-5">
            @csrf @method('PUT')
            <x-ui.input label="Judul" name="title" value="{{ old('title',$announcement->title) }}" required />
            <x-ui.rich-text-editor label="Konten" name="content" :value="old('content', $announcement->content)" required />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.select label="Status" name="status" :value="old('status',$announcement->status->value)" :options="['draft'=>'Draft','published'=>'Published','archived'=>'Archived']" required />
                <x-ui.input label="Jadwal Publikasi" name="published_at" type="datetime-local" value="{{ old('published_at',$announcement->published_at?->timezone('Asia/Jakarta')->format('Y-m-d\TH:i')) }}" />
            </div>
            <div class="flex justify-end gap-3">
                <x-ui.button variant="ghost" href="{{ route('admin.announcements.index') }}">Batal</x-ui.button>
                <x-ui.button type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-admin.layouts.app>
