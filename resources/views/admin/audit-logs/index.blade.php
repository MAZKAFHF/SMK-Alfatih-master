<x-admin.layouts.app :title="'Audit Logs'">
    <x-admin.page-head title="Audit Log" context="Riwayat aksi administratif — hanya baca, tidak dapat diubah." />
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
                @php
                    $actionLabel = match($log->action) {
                        'program_create' => 'Tambah program',
                        'program_update' => 'Ubah program',
                        'program_delete' => 'Hapus program',
                        'program_restore' => 'Pulihkan program',
                        'program_force_delete' => 'Hapus permanen program',
                        'news_create' => 'Tambah berita',
                        'news_update' => 'Ubah berita',
                        'news_delete' => 'Hapus berita',
                        'news_restore' => 'Pulihkan berita',
                        'news_force_delete' => 'Hapus permanen berita',
                        'announcement_create' => 'Tambah pengumuman',
                        'announcement_update' => 'Ubah pengumuman',
                        'announcement_delete' => 'Hapus pengumuman',
                        'announcement_restore' => 'Pulihkan pengumuman',
                        'announcement_force_delete' => 'Hapus permanen pengumuman',
                        'gallery_create' => 'Tambah galeri',
                        'gallery_update' => 'Ubah galeri',
                        'gallery_delete' => 'Hapus galeri',
                        'gallery_restore' => 'Pulihkan galeri',
                        'gallery_force_delete' => 'Hapus permanen galeri',
                        'page_create' => 'Tambah halaman',
                        'page_update' => 'Ubah halaman',
                        'page_delete' => 'Hapus halaman',
                        'page_restore' => 'Pulihkan halaman',
                        'page_force_delete' => 'Hapus permanen halaman',
                        'contact_delete' => 'Hapus pesan',
                        'contact_restore' => 'Pulihkan pesan',
                        'contact_force_delete' => 'Hapus permanen pesan',
                        'ppdb_status_update' => 'Ubah status PPDB',
                        'ppdb_delete' => 'Hapus pendaftar',
                        'ppdb_restore' => 'Pulihkan pendaftar',
                        'ppdb_destroy_all' => 'Hapus massal PPDB',
                        'ppdb_force_delete' => 'Hapus permanen pendaftar',
                        'ppdb_export' => 'Ekspor data PPDB',
                        'ppdb_settings_update' => 'Ubah pengaturan PPDB',
                        'settings_update' => 'Ubah pengaturan',
                        'user_update' => 'Ubah user',
                        'user_activate' => 'Aktifkan user',
                        'user_deactivate' => 'Nonaktifkan user',
                        'user_delete' => 'Hapus user',
                        default => str_replace('_', ' ', $log->action),
                    };
                @endphp
                <tr>
                    <td class="px-4 py-3 text-xs">{{ $log->created_at?->translatedFormat('d M Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm">{{ $log->user?->name ?? 'System' }}<div class="text-xs text-slate-400">{{ $log->user?->email }}</div></td>
                    <td class="px-4 py-3"><x-ui.badge color="slate" size="sm">{{ $actionLabel }}</x-ui.badge></td>
                    <td class="px-4 py-3 text-sm">{{ $log->auditable_label ?? '—' }}<div class="text-xs text-slate-500">{{ $log->auditable_type ? class_basename($log->auditable_type).'#'.$log->auditable_id : '' }}</div></td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $log->ip_address }}</td>
                </tr>
            @endforeach
        </x-ui.table>
        <div class="mt-6">{{ $logs->links() }}</div>
    @endif
</x-admin.layouts.app>
