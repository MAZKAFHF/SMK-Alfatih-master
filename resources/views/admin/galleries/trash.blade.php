<x-admin.layouts.app :title="'Trash Galeri'">
    <div class="mb-5"><a href="{{ route('admin.galleries.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Trash — Galeri</h1></div>
    @if($galleries->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Trash kosong" /></x-ui.card>
    @else
        <x-ui.table :head="['Judul','Kategori','Dihapus','Aksi']">
            @foreach($galleries as $g)
                <tr>
                    <td class="px-4 py-3 font-semibold">{{ $g->title }}</td>
                    <td class="px-4 py-3 text-xs">{{ $g->category }}</td>
                    <td class="px-4 py-3 text-xs">{{ $g->deleted_at->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <form method="POST" action="{{ route('admin.galleries.restore',$g->id) }}" class="inline">@csrf <x-ui.button size="sm" type="submit">Restore</x-ui.button></form>
                        <x-ui.button size="sm" variant="danger" onclick="confirmDialog({title:'Hapus permanen?', message:'{{ $g->title }}', formAction:'{{ route('admin.galleries.force-delete',$g->id) }}', method:'DELETE'})">Hapus Permanen</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $galleries->links() }}</div>
    @endif
</x-admin.layouts.app>
