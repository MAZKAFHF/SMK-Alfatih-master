@props(['title' => null])

<x-layouts.app :title="$title">
    <div class="border-b border-slate-200 bg-cloud-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <div class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                <span class="inline-flex size-8 items-center justify-center rounded-lg bg-primary-600 text-white">AF</span>
                <span>PPDB PORTAL <span class="font-medium text-slate-500">— {{ auth()->user()?->name }}</span></span>
            </div>
            <nav class="flex flex-wrap items-center gap-1 text-sm" aria-label="Navigasi portal">
                <a href="{{ route('portal.dashboard') }}" class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('portal.dashboard') ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300' }}">Beranda</a>
                <a href="{{ route('portal.applications.create') }}" class="rounded-lg px-3 py-2 font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300">+ Siswa Baru</a>
                <a href="{{ route('portal.notifications') }}" class="rounded-lg px-3 py-2 font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300">Notifikasi</a>
                <form method="POST" action="{{ route('portal.logout') }}">@csrf<button class="rounded-lg px-3 py-2 font-medium text-red-600 hover:bg-red-50">Keluar</button></form>
            </nav>
        </div>
    </div>
    <main id="portal-content" class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">
        @if(session('success'))<div class="mb-4"><x-ui.alert variant="success" :title="session('success')" dismissible>{{ session('success') }}</x-ui.alert></div>@endif
        @if(session('error'))<div class="mb-4"><x-ui.alert variant="danger" title="Terjadi kendala" dismissible>{{ session('error') }}</x-ui.alert></div>@endif
        @if(session('info'))<div class="mb-4"><x-ui.alert variant="info" title="Info" dismissible>{{ session('info') }}</x-ui.alert></div>@endif
        {{ $slot }}
    </main>
</x-layouts.app>
