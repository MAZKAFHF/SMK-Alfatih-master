<x-layouts.app :title="'PPDB Ditutup'">
    <x-page-header title="PPDB Ditutup" subtitle="Penerimaan peserta didik baru saat ini tidak menerima pendaftaran" :breadcrumbs="[['label'=>'Beranda','url'=>route('home')],['label'=>'PPDB','url'=>route('ppdb.index')],['label'=>'Ditutup']]" />
    <section class="py-16">
        <div class="mx-auto max-w-xl px-4 text-center sm:px-6">
            <x-ui.card class="p-8">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30"> <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Pendaftaran {{ $ppdb->academic_year }} {{ $ppdb->statusLabel() }}</h2>
                @if($ppdb->opens_at && now()->lt($ppdb->opens_at))
                    <p class="mt-2 text-sm text-slate-500">Akan dibuka pada {{ $ppdb->opens_at->translatedFormat('d M Y H:i') }} WIB.</p>
                @elseif($ppdb->closes_at)
                    <p class="mt-2 text-sm text-slate-500">Telah ditutup pada {{ $ppdb->closes_at->translatedFormat('d M Y H:i') }} WIB.</p>
                @endif
                @if($ppdb->announcement)
                    <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $ppdb->announcement }}</p>
                @endif
                @if($ppdb->contact_info)
                    <p class="mt-4 text-sm text-slate-600 dark:text-slate-400">Hubungi: {{ $ppdb->contact_info }}</p>
                @endif
                <div class="mt-6 flex justify-center gap-3">
                    <x-ui.button variant="outline" href="{{ route('ppdb.status') }}">Cek Status</x-ui.button>
                    <x-ui.button href="{{ route('home') }}">Kembali ke Beranda</x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </section>
</x-layouts.app>
