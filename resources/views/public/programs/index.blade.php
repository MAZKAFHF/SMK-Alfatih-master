<x-layouts.app :title="'Program Keahlian'">
    <x-page-header
        title="Program Keahlian"
        subtitle="Pilih kompetensi yang sesuai dengan minat dan bakatmu. Semua program dirancang untuk membekali siswa dengan keterampilan siap kerja."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Program Keahlian'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($programs->isEmpty())
                <x-ui.empty-state title="Belum ada program keahlian" description="Program keahlian akan segera diumumkan." />
            @endif

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($programs as $program)
                    <a href="{{ route('programs.show', $program) }}" class="group">
                        <x-ui.card padding="false" hover="true" class="h-full overflow-hidden">
                            <x-thumb :src="$program->image" ratio="aspect-[4/3]" :alt="$program->name" />
                            <div class="p-5">
                                <h2 class="text-lg font-bold text-slate-900 transition-colors group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-400">{{ $program->name }}</h2>
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $program->short_description }}</p>
                                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary-600">
                                    Selengkapnya
                                    <svg class="size-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </x-ui.card>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
