<x-admin.layouts.app :title="'Trash Overview'">
    <h1 class="text-xl font-extrabold">Trash — Semua Terhapus</h1>
    <p class="mt-1 text-sm text-slate-500">Kembalikan atau hapus permanen. Hanya superadmin dapat hapus permanen.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @php
            $counts = [
                'Program' => \App\Models\Program::onlyTrashed()->count(),
                'Berita' => \App\Models\News::onlyTrashed()->count(),
                'Galeri' => \App\Models\Gallery::onlyTrashed()->count(),
                'Pengumuman' => \App\Models\Announcement::onlyTrashed()->count(),
                'Halaman' => \App\Models\Page::onlyTrashed()->count(),
                'Pesan Masuk' => \App\Models\ContactMessage::onlyTrashed()->count(),
                'Pendaftar PPDB' => \App\Models\PPDBRegistration::onlyTrashed()->count(),
            ];
        @endphp
        @foreach($counts as $label=>$count)
            <x-ui.card class="p-5 text-center">
                <p class="text-2xl font-extrabold {{ $count>0 ? 'text-amber-600' : 'text-slate-400' }}">{{ $count }}</p>
                <p class="mt-1 text-sm font-medium text-slate-600 dark:text-slate-400">{{ $label }}</p>
                @if($count>0)
                    @php $routeMap = ['Program'=>'admin.programs.trash','Berita'=>'admin.news.trash','Galeri'=>'admin.galleries.trash','Pengumuman'=>'admin.announcements.trash','Halaman'=>'admin.pages.trash','Pesan Masuk'=>'admin.contact-messages.trash','Pendaftar PPDB'=>'admin.registrations.trash']; @endphp
                    <x-ui.button size="sm" variant="outline" href="{{ route($routeMap[$label]) }}" class="mt-3">Lihat</x-ui.button>
                @endif
            </x-ui.card>
        @endforeach
    </div>
</x-admin.layouts.app>
