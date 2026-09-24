<x-layouts.app :title="$program->name" :description="$program->short_description">
    <x-page-header
        :title="$program->name"
        :subtitle="$program->short_description"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Program Keahlian', 'url' => route('programs.index')],
            ['label' => $program->name],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <x-thumb :src="$program->image" ratio="aspect-video" class="rounded-2xl" :alt="$program->name" />

                    <article class="prose-content mt-8">{!! nl2br(e($program->description)) !!}</article>
                </div>

                <aside class="space-y-6">
                    <x-ui.card>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Cari tahu program lainnya</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($otherPrograms as $other)
                                <a href="{{ route('programs.show', $other) }}" class="group flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 transition-colors hover:border-primary-300 hover:bg-primary-50/50 dark:border-slate-700 dark:hover:border-primary-700 dark:hover:bg-primary-950/50">
                                    <span class="text-sm font-semibold text-slate-700 group-hover:text-primary-700 dark:text-slate-300 dark:group-hover:text-primary-400">{{ $other->name }}</span>
                                    <svg class="size-4 text-slate-400 transition-transform group-hover:translate-x-0.5 group-hover:text-primary-600 dark:text-slate-500 dark:group-hover:text-primary-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </x-ui.card>

                    <x-ui.card class="bg-gradient-to-br from-primary-700 to-primary-900 text-white border-transparent">
                        <h2 class="text-base font-bold">Tertarik dengan {{ $program->name }}?</h2>
                        <p class="mt-2 text-sm leading-relaxed text-primary-100">Daftar sekarang melalui PPDB online dan wujudkan masa depanmu.</p>
                        <div class="mt-5">
                            <x-ui.button variant="accent" href="{{ route('ppdb.index') }}">Daftar PPDB</x-ui.button>
                        </div>
                    </x-ui.card>
                </aside>
            </div>
        </div>
    </section>
</x-layouts.app>
