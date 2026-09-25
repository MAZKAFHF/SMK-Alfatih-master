<x-admin.layouts.app :title="'Halaman Statis'">
    <x-admin.page-head title="Halaman" context="Profil, Sejarah, Visi-Misi, Sambutan, Fasilitas & custom.">
        @if($trashedCount>0)<x-ui.button variant="ghost" size="sm" href="{{ route('admin.pages.trash') }}">Trash ({{ $trashedCount }})</x-ui.button>@endif
        <x-ui.button href="{{ route('admin.pages.create') }}">+ Tambah Halaman</x-ui.button>
    </x-admin.page-head>
    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.pages.index') }}" class="flex gap-3">
            <div class="flex-1"><x-ui.input name="search" placeholder="Cari judul/slug..." value="{{ request('search') }}" /></div>
            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
        </form>
    </x-ui.card>
    <x-ui.table :head="['Judul','Slug','Status','Urutan','Aksi']">
        @foreach($pages as $page)
            <tr>
                <td class="px-4 py-3 font-semibold">{{ $page->title }}</td>
                <td class="px-4 py-3 font-mono text-xs">{{ $page->slug }}</td>
                <td class="px-4 py-3"><x-ui.badge :color="$page->status->value==='published'?'green':'slate'" size="sm">{{ $page->status->label() }}</x-ui.badge></td>
                <td class="px-4 py-3">{{ $page->order }}</td>
                <td class="px-4 py-3 text-right space-x-1">
                    <a href="{{ route('pages.show',$page->slug) }}" target="_blank" class="text-xs text-primary-600 hover:underline">Lihat</a>
                    <x-ui.button size="sm" variant="outline" href="{{ route('admin.pages.edit',$page) }}">Edit</x-ui.button>
                    <x-ui.button size="sm" variant="danger" onclick="confirmDialog({title:'Hapus halaman?', formAction:'{{ route('admin.pages.destroy',$page) }}', method:'DELETE'})">Hapus</x-ui.button>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
    <div class="mt-6">{{ $pages->links() }}</div>
</x-admin.layouts.app>
