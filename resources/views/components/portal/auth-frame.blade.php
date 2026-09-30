@props(['title'])

<x-layouts.app :title="$title" surface="portal" :chrome="false">
    <header class="border-b border-emerald-950/10 bg-white/80 backdrop-blur-xl dark:border-white/10 dark:bg-[#07120e]/80">
        <div class="mx-auto flex h-[4.5rem] max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <x-brand-logo :decorative="true" />
                <span class="leading-none"><span class="block font-display text-sm font-extrabold text-slate-950 dark:text-white">ALFATIH<span class="text-primary-700 dark:text-tech-400">//PORTAL</span></span><span class="mt-1 block text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Akses calon siswa</span></span>
            </a>
            <div class="flex items-center gap-1"><x-ui.theme-toggle /><a href="{{ route('home') }}" class="hidden rounded-xl px-3 py-2 text-xs font-bold text-slate-600 hover:bg-white sm:inline-flex dark:text-slate-300 dark:hover:bg-white/5">Kembali ke website</a></div>
        </div>
    </header>
    <div class="relative flex flex-1 items-center justify-center overflow-hidden px-4 py-10 sm:py-14">
        <div class="pointer-events-none absolute -left-32 top-0 size-96 rounded-full bg-emerald-200/30 blur-3xl dark:bg-emerald-900/20" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-32 bottom-0 size-96 rounded-full bg-gold-200/30 blur-3xl dark:bg-gold-900/10" aria-hidden="true"></div>
        <div class="relative w-full max-w-md">{{ $slot }}</div>
    </div>
    <footer class="px-4 py-5 text-center text-xs text-slate-500 dark:text-slate-400">Portal resmi {{ config('app.name') }}</footer>
</x-layouts.app>
