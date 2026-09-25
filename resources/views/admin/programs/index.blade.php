<x-admin.layouts.app :title="'Program Keahlian'">
    <x-admin.page-head title="Program Keahlian" context="Kelola 4 program (PPLG, Multimedia, DKV, TJKT) & tampilkan di halaman publik.">
        @if($trashedCount>0)
            <x-ui.button variant="ghost" size="sm" href="{{ route('admin.programs.trash') }}">Trash ({{ $trashedCount }})</x-ui.button>
        @endif
        <x-ui.button href="{{ route('admin.programs.create') }}">+ Tambah Program</x-ui.button>
    </x-admin.page-head>

    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.programs.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1"><x-ui.input name="search" placeholder="Cari nama atau slug..." value="{{ request('search') }}" /></div>
            <div class="sm:w-44">
                <x-ui.select name="status" :value="request('status')" :options="['active'=>'Aktif','inactive'=>'Nonaktif']" placeholder="Semua status"><option value="" {{ blank(request('status')) ? 'selected' : '' }}>Semua status</option></x-ui.select>
            </div>
            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
            @if(request()->has('search')||request()->has('status'))<x-ui.button variant="ghost" href="{{ route('admin.programs.index') }}">Reset</x-ui.button>@endif
        </form>
    </x-ui.card>

    @if($programs->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Tidak ada program" description="Belum ada program yang cocok." /></x-ui.card>
    @else
        <x-ui.table :head="['Program','Slug','Status','Urutan','Aksi']">
            @foreach($programs as $program)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($program->image)
                                <img src="{{ $program->image }}" alt="{{ $program->name }}" class="size-10 rounded-lg object-cover" loading="lazy" width="40" height="40" />
                            @else
                                <span class="flex size-10 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400">—</span>
                            @endif
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $program->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">{{ $program->short_description }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $program->slug }}</td>
                    <td class="px-4 py-3"><x-ui.badge :color="$program->status->value==='active' ? 'green' : 'slate'" size="sm" dot>{{ $program->status->label() }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $program->order }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <x-ui.button variant="outline" size="sm" href="{{ route('admin.programs.edit',$program) }}">Edit</x-ui.button>
                        <x-ui.button variant="danger" size="sm" onclick="confirmDialog({title:'Hapus program?', message:'{{ $program->name }} akan dipindahkan ke Trash.', formAction:'{{ route('admin.programs.destroy',$program) }}', method:'DELETE'})">Hapus</x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $programs->links() }}</div>
    @endif
</x-admin.layouts.app>
