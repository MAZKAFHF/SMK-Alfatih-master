<x-admin.layouts.app :title="'Trash Programs'">
    <div class="mb-5"><a href="{{ route('admin.programs.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400">&larr; Kembali</a>
        <h1 class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">Trash — Program</h1></div>
    @if($programs->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Trash kosong" description="Tidak ada program terhapus." /></x-ui.card>
    @else
        <x-ui.table :head="['Program','Dihapus','Aksi']">
            @foreach($programs as $p)
                <tr>
                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $p->name }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $p->deleted_at->translatedFormat('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <form method="POST" action="{{ route('admin.programs.restore',$p->id) }}" class="inline">@csrf <x-ui.button size="sm" type="submit" variant="secondary">Restore</x-ui.button></form>
                        <x-ui.button size="sm" variant="danger" onclick="confirmDialog({title:'Hapus permanen?', message:'Hapus permanen {{ $p->name }}? Tidak dapat dikembalikan.', formAction:'{{ route('admin.programs.force-delete',$p->id) }}', method:'DELETE'})">Hapus Permanen</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $programs->links() }}</div>
    @endif
</x-admin.layouts.app>
