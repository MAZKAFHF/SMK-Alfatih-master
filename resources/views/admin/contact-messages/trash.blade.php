<x-admin.layouts.app :title="'Trash Pesan'">
    <div class="mb-5"><a href="{{ route('admin.contact-messages.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Trash — Pesan Masuk</h1></div>
    @if($messages->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Trash kosong" /></x-ui.card>
    @else
        <x-ui.table :head="['Subjek','Pengirim','Dihapus','Aksi']">
            @foreach($messages as $m)
                <tr>
                    <td class="px-4 py-3 font-semibold">{{ $m->subject }}</td>
                    <td class="px-4 py-3 text-sm">{{ $m->name }}</td>
                    <td class="px-4 py-3 text-xs">{{ $m->deleted_at->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <form method="POST" action="{{ route('admin.contact-messages.restore',$m->id) }}" class="inline">@csrf <x-ui.button size="sm" type="submit">Restore</x-ui.button></form>
                        <x-ui.button size="sm" variant="danger" onclick="confirmDialog({title:'Hapus permanen?', formAction:'{{ route('admin.contact-messages.force-delete',$m->id) }}', method:'DELETE'})">Hapus Permanen</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $messages->links() }}</div>
    @endif
</x-admin.layouts.app>
