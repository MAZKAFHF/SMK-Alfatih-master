<x-admin.layouts.app :title="'Berita'">
    <x-admin.page-head title="Berita" context="Kelola artikel & penjadwalan publikasi.">
        @if($trashedCount>0)<x-ui.button variant="ghost" size="sm" href="{{ route('admin.news.trash') }}">Trash ({{ $trashedCount }})</x-ui.button>@endif
        <x-ui.button href="{{ route('admin.news.create') }}">+ Tambah Berita</x-ui.button>
    </x-admin.page-head>
    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.news.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1"><x-ui.input name="search" placeholder="Cari judul/slug..." value="{{ request('search') }}" /></div>
            <div class="sm:w-44"><x-ui.select name="status" :value="request('status')" :options="['draft'=>'Draft','published'=>'Published','archived'=>'Archived']" placeholder="Semua status"></x-ui.select></div>
            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
        </form>
    </x-ui.card>
    @if($news->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Tidak ada berita" /></x-ui.card>
    @else
        <x-ui.table :head="['Judul','Slug','Penulis','Status','Publikasi','Aksi']">
            @foreach($news as $item)
                <tr>
                    <td class="px-4 py-3">
                        <div class="flex gap-3">
                            @if($item->thumbnail)<img src="{{ $item->thumbnail }}" class="size-10 rounded object-cover" loading="lazy" width="40" height="40" alt="" />@endif
                            <div><p class="font-semibold text-slate-900 dark:text-white line-clamp-1">{{ $item->title }}</p><p class="text-xs text-slate-500 line-clamp-1">{{ \Illuminate\Support\Str::limit(strip_tags($item->content),60) }}</p></div>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $item->slug }}</td>
                    <td class="px-4 py-3 text-sm">{{ $item->author?->name ?? '-' }}</td>
                    <td class="px-4 py-3"><x-ui.badge :color="$item->status->value==='published'?'green':($item->status->value==='draft'?'slate':'amber')" size="sm">{{ $item->status->label() }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-xs">{{ $item->published_at?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <x-ui.button variant="outline" size="sm" href="{{ route('admin.news.edit',$item) }}">Edit</x-ui.button>
                        <x-ui.button variant="danger" size="sm" onclick="confirmDialog({title:'Hapus berita?', message:'Pindah ke Trash?', formAction:'{{ route('admin.news.destroy',$item) }}', method:'DELETE'})">Hapus</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $news->links() }}</div>
    @endif
</x-admin.layouts.app>
