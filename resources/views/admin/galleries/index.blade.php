<x-admin.layouts.app :title="'Galeri'">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div><h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Galeri</h1><p class="text-sm text-slate-500">Foto kegiatan &amp; fasilitas.</p></div>
        <div class="flex gap-2">
            @if($trashedCount>0)<x-ui.button variant="ghost" size="sm" href="{{ route('admin.galleries.trash') }}">Trash ({{ $trashedCount }})</x-ui.button>@endif
            <x-ui.button href="{{ route('admin.galleries.create') }}">+ Tambah Foto</x-ui.button>
        </div>
    </div>
    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.galleries.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1"><x-ui.input name="search" placeholder="Cari judul..." value="{{ request('search') }}" /></div>
            <div class="sm:w-32"><x-ui.input name="category" placeholder="Kategori" value="{{ request('category') }}" /></div>
            <div class="sm:w-36"><x-ui.select name="status" :value="request('status')" :options="['published'=>'Published','draft'=>'Draft','archived'=>'Archived']" placeholder="Semua"><option value="">Semua</option></x-ui.select></div>
            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
        </form>
    </x-ui.card>
    @if($galleries->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Belum ada foto" /></x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($galleries as $g)
                <x-ui.card class="overflow-hidden p-0">
                    @if($g->image)<img src="{{ $g->image }}" alt="{{ $g->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy" />@endif
                    <div class="p-4">
                        <p class="font-semibold text-slate-900 dark:text-white line-clamp-1">{{ $g->title }}</p>
                        <p class="text-xs text-slate-500">{{ $g->category ?? 'Tanpa kategori' }} • {{ $g->status->label() }}</p>
                        <div class="mt-3 flex gap-1">
                            <x-ui.button size="sm" variant="outline" href="{{ route('admin.galleries.edit',$g) }}">Edit</x-ui.button>
                            <x-ui.button size="sm" variant="danger" onclick="confirmDialog({title:'Hapus?', message:'{{ $g->title }}', formAction:'{{ route('admin.galleries.destroy',$g) }}', method:'DELETE'})">Hapus</x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-6">{{ $galleries->links() }}</div>
    @endif
</x-admin.layouts.app>
