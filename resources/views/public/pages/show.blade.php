<x-layouts.app :title="$page->title" :description="$page->meta_description">
    <x-page-header
        :title="$page->title"
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => $page->title],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if ($page->image)
                <x-thumb :src="$page->image" ratio="aspect-video" class="mb-8 rounded-2xl" :alt="$page->title" />
            @endif

            <article class="prose-content">{!! $page->content !!}</article>

            <div class="mt-12 border-t border-slate-200 pt-8 dark:border-slate-700/50">
                <x-ui.button variant="outline" href="{{ route('contact.index') }}">Hubungi Kami</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.app>
