<x-admin.layouts.app :title="'Audit Logs'">
    <div class="mb-5"><h1 class="text-xl font-extrabold">Audit Log</h1><p class="text-sm text-slate-500">Riwayat aksi administratif — immutable.</p></div>
    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1"><x-ui.input name="search" placeholder="Cari label atau user..." value="{{ request('search') }}" /></div>
            <div class="sm:w-48"><x-ui.select name="action" :value="request('action')" :options="$actions->mapWithKeys(fn($a)=>[$a=>$a])->all()" placeholder="Semua aksi"><option value="">Semua aksi</option></x-ui.select></div>
            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
        </form>
    </x-ui.card>
    @if($logs->isEmpty())
        <x-ui.card class="p-6"><x-ui.empty-state title="Belum ada log" /></x-ui.card>
    @else
        <x-ui.table :head="['Waktu','User','Aksi','Target','IP']">
            @foreach($logs as $log)
                <tr>
                    <td class="px-4 py-3 text-xs">{{ $log->created_at?->translatedFormat('d M Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm">{{ $log->user?->name ?? 'System' }}<div class="text-xs text-slate-400">{{ $log->user?->email }}</div></td>
                    <td class="px-4 py-3"><x-ui.badge color="slate" size="sm">{{ $log->action }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-sm">{{ $log->auditable_type ? class_basename($log->auditable_type).'#'.$log->auditable_id : '-' }}<div class="text-xs text-slate-500">{{ $log->auditable_label }}</div></td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $log->ip_address }}</td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $logs->links() }}</div>
    @endif
</x-admin.layouts.app>
