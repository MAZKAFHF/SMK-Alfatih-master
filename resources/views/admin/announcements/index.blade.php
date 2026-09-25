<x-admin.layouts.app :title="'Pengumuman'">
    <x-admin.page-head title="Pengumuman" context="Informasi resmi sekolah.">
        @if($trashedCount>0)<x-ui.button variant="ghost" size="sm" href="{{ route('admin.announcements.trash') }}">Trash ({{ $trashedCount }})</x-ui.button>@endif
        <x-ui.button href="{{ route('admin.announcements.create') }}">+ Tambah</x-ui.button>
    </x-admin.page-head>
    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.announcements.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1"><x-ui.input name="search" placeholder="Cari judul..." value="{{ request('search') }}" /></div>
            <div class="sm:w-36"><x-ui.select name="status" :value="request('status')" :options="['draft'=>'Draft','published'=>'Published','archived'=>'Archived']" placeholder="Semua"><option value="">Semua</option></x-ui.select></div>
            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
        </form>
    </x-ui.card>
    @if($announcements->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Belum ada pengumuman" /></x-ui.card>
    @else
        <x-ui.table :head="['Judul','Status','Publikasi','Aksi']">
            @foreach($announcements as $a)
                <tr>
                    <td class="px-4 py-3 font-semibold">{{ $a->title }}</td>
                    <td class="px-4 py-3"><x-ui.badge :color="$a->status->value==='published'?'green':'slate'" size="sm">{{ $a->status->label() }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-xs">{{ $a->published_at?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <x-ui.button size="sm" variant="outline" href="{{ route('admin.announcements.edit',$a) }}">Edit</x-ui.button>
                        <x-ui.button size="sm" variant="danger" onclick="confirmDialog({title:'Hapus?', formAction:'{{ route('admin.announcements.destroy',$a) }}', method:'DELETE'})">Hapus</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $announcements->links() }}</div>
    @endif
</x-admin.layouts.app>
