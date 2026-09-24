<x-admin.layouts.app :title="'Trash Pengumuman'">
    <div class="mb-5"><a href="{{ route('admin.announcements.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Trash — Pengumuman</h1></div>
    @if($announcements->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Trash kosong" /></x-ui.card>
    @else
        <x-ui.table :head="['Judul','Dihapus','Aksi']">
            @foreach($announcements as $a)
                <tr>
                    <td class="px-4 py-3 font-semibold">{{ $a->title }}</td>
                    <td class="px-4 py-3 text-xs">{{ $a->deleted_at->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <form method="POST" action="{{ route('admin.announcements.restore',$a->id) }}" class="inline">@csrf <x-ui.button size="sm" type="submit">Restore</x-ui.button></form>
                        <x-ui.button size="sm" variant="danger" onclick="confirmDialog({title:'Hapus permanen?', formAction:'{{ route('admin.announcements.force-delete',$a->id) }}', method:'DELETE'})">Hapus Permanen</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $announcements->links() }}</div>
    @endif
</x-admin.layouts.app>
