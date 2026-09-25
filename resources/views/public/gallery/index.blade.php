<x-layouts.app :title="'Galeri'">
    <x-page-header
        title="Galeri Sekolah"
        subtitle="Dokumentasi kegiatan, fasilitas, dan prestasi SMK Tahfizh Al-Fatih."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Galeri'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap justify-center gap-2" role="tablist" aria-label="Filter kategori galeri">
                    <a
                        href="{{ route('gallery.index') }}"
                        data-active="{{ blank(request('category')) ? 'true' : 'false' }}"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition-colors data-[active=true]:bg-primary-600 data-[active=true]:text-white data-[active=false]:bg-white data-[active=false]:text-slate-600 data-[active=false]:ring-1 data-[active=false]:ring-inset data-[active=false]:ring-slate-300 data-[active=false]:hover:bg-slate-50 dark:data-[active=false]:bg-slate-800 dark:data-[active=false]:text-slate-400 dark:data-[active=false]:ring-slate-600 dark:data-[active=false]:hover:bg-slate-700"
                    >Semua</a>
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('gallery.index', ['category' => $category]) }}"
                            data-active="{{ request('category') === $category ? 'true' : 'false' }}"
                            class="rounded-full px-4 py-1.5 text-sm font-medium transition-colors data-[active=true]:bg-primary-600 data-[active=true]:text-white data-[active=false]:bg-white data-[active=false]:text-slate-600 data-[active=false]:ring-1 data-[active=false]:ring-inset data-[active=false]:ring-slate-300 data-[active=false]:hover:bg-slate-50 dark:data-[active=false]:bg-slate-800 dark:data-[active=false]:text-slate-400 dark:data-[active=false]:ring-slate-600 dark:data-[active=false]:hover:bg-slate-700"
                        >{{ $category }}</a>
                    @endforeach
                </div>
            @endif

            @if ($galleries->isEmpty())
                <div class="mt-8">
                    <x-ui.empty-state title="Belum ada foto" description="Galeri foto akan segera hadir." />
                </div>
            @endif

            <div class="reveal mt-10 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4" data-gallery-grid>
                @foreach ($galleries as $gallery)
                    <button
                        type="button"
                        data-gallery-item
                        data-category="{{ $gallery->category }}"
                        data-title="{{ $gallery->title }}"
                        data-src="{{ $gallery->image }}"
                        class="group relative block cursor-zoom-in overflow-hidden rounded-xl text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                        aria-label="Lihat foto: {{ $gallery->title }}"
                    >
                        <div class="relative aspect-[4/3]">
                            @if ($gallery->image)
                                <img src="{{ $gallery->image }}" alt="{{ $gallery->title }}" loading="lazy" width="400" height="300" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary-100 via-white to-accent-100">
                                    <svg class="size-10 text-primary-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.87a4.5 4.5 0 11-5.313-5.313 4.5 4.5 0 015.313 5.313z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/70 to-transparent p-3 pt-10">
                            <p class="text-sm font-medium text-white">{{ $gallery->title }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
            <div class="mt-8 flex justify-center">
                {{ $galleries->links() }}
            </div>
        </div>
    </section>

    {{-- Lightbox --}}
    <div data-lightbox class="fixed inset-0 z-[85] hidden" role="dialog" aria-modal="true" aria-label="Pratinjau galeri">
        <div data-lightbox-backdrop class="absolute inset-0 bg-slate-950/90" aria-hidden="true"></div>
        <div class="relative flex h-full w-full items-center justify-center p-4 sm:p-8">
            <button type="button" data-lightbox-close class="absolute right-4 top-4 z-10 rounded-full bg-white/10 p-2 text-white transition-colors hover:bg-white/20" aria-label="Tutup galeri">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <figure class="max-h-full max-w-4xl">
                <div data-lightbox-image class="mx-auto flex max-h-[75vh] max-w-full items-center justify-center overflow-hidden rounded-xl bg-white/5"></div>
                <figcaption class="mt-4 text-center text-sm font-medium text-white" data-lightbox-caption></figcaption>
            </figure>
        </div>
    </div>
</x-layouts.app>
