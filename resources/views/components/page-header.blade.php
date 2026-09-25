@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<section class="relative overflow-hidden bg-navy-900">
    <div class="tech-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
    <x-motif-geometric class="absolute -right-10 -top-10 size-52 text-tech-500/15" />
    <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-primary-600 via-gold-500 to-energy-500" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        @if (count($breadcrumbs) > 0)
            <x-ui.breadcrumb :items="$breadcrumbs" class="mb-4 [&_a]:text-slate-300 [&_a:hover]:text-gold-400 [&_span]:text-white [&_svg]:text-gold-500" />
        @endif

        <h1 class="max-w-3xl font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ $title }}</h1>
        <span class="mt-4 block h-1 w-16 rounded-full bg-gold-500" aria-hidden="true"></span>

        @if ($subtitle)
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-300 sm:text-base">{{ $subtitle }}</p>
        @endif
    </div>
</section>
