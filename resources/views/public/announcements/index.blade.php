<x-layouts.app :title="'Pengumuman'">
    <x-page-header
        title="Pengumuman"
        subtitle="Informasi resmi dan pemberitahuan penting dari SMK Tahfizh Al-Fatih."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Pengumuman'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if ($announcements->isEmpty())
                <x-ui.empty-state title="Belum ada pengumuman" description="Pengumuman akan segera hadir. Silakan kunjungi kembali." />
            @endif

            <div class="space-y-4">
                @foreach ($announcements as $announcement)
                    <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-start gap-4">
                            <span class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-full bg-accent-50 text-accent-600 dark:bg-accent-950 dark:text-accent-400" aria-hidden="true">
                                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.25 4.5a2.25 2.25 0 012.25-2.25h9a2.25 2.25 0 012.25 2.25v13.5a.75.75 0 01-.75.75H6a.75.75 0 01-.75-.75V4.5zM6 18.75h12v2.25a.75.75 0 01-.75.75h-9a.75.75 0 01-.75-.75v-2.25z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h2 class="font-semibold text-slate-900 dark:text-white">{{ $announcement->title }}</h2>
                                    <time datetime="{{ $announcement->published_at?->toIso8601String() }}" class="text-xs font-medium text-primary-600 dark:text-primary-400">
                                        {{ $announcement->published_at?->format('d M Y') }}
                                    </time>
                                </div>
                                <div class="prose-content mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{!! $announcement->content !!}</div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $announcements->links() }}
            </div>
        </div>
    </section>
</x-layouts.app>
