@props([
    'code' => '500',
    'title' => 'Terjadi Kesalahan',
    'message' => 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
])

<x-layouts.app :title="$code" :chrome="false" robots="noindex, nofollow, noarchive, nosnippet">
    <div class="relative flex min-h-[70vh] items-center justify-center overflow-hidden px-4 py-16">
        <div class="tech-grid-light pointer-events-none absolute inset-0 dark:hidden" aria-hidden="true"></div>
        <div class="tech-grid pointer-events-none absolute inset-0 hidden dark:block" aria-hidden="true"></div>
        <x-motif-geometric class="absolute left-1/2 top-8 size-40 -translate-x-1/2 text-primary-600/10 dark:text-tech-500/10" />
        <div class="relative mx-auto max-w-md text-center">
            <p class="font-display text-7xl font-extrabold tracking-tight text-forest-800 dark:text-white">{{ $code }}</p>
            <span class="mx-auto mt-4 block h-1 w-16 rounded-full bg-gradient-to-r from-primary-600 via-gold-500 to-energy-500" aria-hidden="true"></span>
            <h1 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white">{{ $title }}</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $message }}</p>
            <div class="mt-8 flex items-center justify-center gap-3">
                <x-ui.button href="{{ route('home') }}" class="clip-corner-sm">Kembali ke Beranda</x-ui.button>
                <x-ui.button variant="outline" onclick="history.back()">Kembali</x-ui.button>
            </div>
        </div>
    </div>
</x-layouts.app>
