<x-admin.layouts.app :title="'Trash Berita'">
    <div class="mb-5"><a href="{{ route('admin.news.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Kembali</a>
        <h1 class="mt-2 text-xl font-extrabold">Trash — Berita</h1></div>
    @if($news->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Trash kosong" /></x-ui.card>
    @else
        <x-ui.table :head="['Judul','Dihapus','Aksi']">
            @foreach($news as $n)
                <tr>
                    <td class="px-4 py-3 font-semibold">{{ $n->title }}</td>
                    <td class="px-4 py-3 text-xs">{{ $n->deleted_at->translatedFormat('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <form method="POST" novalidate action="{{ route('admin.news.restore',$n->id) }}" class="inline">@csrf <x-ui.button size="sm" type="submit">Restore</x-ui.button></form>
                        <x-ui.button size="sm" variant="danger" onclick="confirmDialog({title:'Hapus permanen?', message:'{{ $n->title }}', formAction:'{{ route('admin.news.force-delete',$n->id) }}', method:'DELETE'})">Hapus Permanen</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $news->links() }}</div>
    @endif
</x-admin.layouts.app>
