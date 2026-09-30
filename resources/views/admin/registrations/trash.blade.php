<x-admin.layouts.app :title="'Trash PPDB'">
    <div class="mb-5"><a href="{{ route('admin.registrations.index') }}" class="text-sm text-slate-500">&larr; Kembali</a><h1 class="mt-2 text-xl font-extrabold">Trash — Pendaftar PPDB</h1></div>
    @if($registrations->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Trash kosong" /></x-ui.card>
    @else
        <x-ui.table :head="['No','Nama','Program','Dihapus','Aksi']">
            @foreach($registrations as $r)
                <tr>
                    <td class="px-4 py-3 font-mono text-xs">{{ $r->registration_number }}</td>
                    <td class="px-4 py-3 font-semibold">{{ $r->name }}</td>
                    <td class="px-4 py-3 text-sm">{{ $r->program?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $r->deleted_at->translatedFormat('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <form method="POST" novalidate action="{{ route('admin.registrations.restore',$r->id) }}" class="inline">@csrf <x-ui.button size="sm" type="submit">Restore</x-ui.button></form>
                        @if(auth()->user()->is_superadmin)
                        <form method="POST" action="{{ route('admin.registrations.force-delete',$r->id) }}" class="mt-2 flex items-center justify-end gap-2">
                            @csrf @method('DELETE')
                            <x-ui.input name="admin_code" type="password" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" placeholder="Kode 4 digit" required />
                            <x-ui.button size="sm" variant="danger" type="submit">Hapus Permanen</x-ui.button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $registrations->links() }}</div>
    @endif
</x-admin.layouts.app>
