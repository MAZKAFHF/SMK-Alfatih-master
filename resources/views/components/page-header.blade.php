@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<section class="relative overflow-hidden bg-gradient-to-br from-primary-800 to-primary-950">
    <div class="pointer-events-none absolute -right-16 -top-16 size-64 rounded-full bg-primary-600/30 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-20 -left-20 size-64 rounded-full bg-accent-500/20 blur-3xl" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        @if (count($breadcrumbs) > 0)
            <x-ui.breadcrumb :items="$breadcrumbs" class="mb-4 [&_a]:text-primary-200 [&_a:hover]:text-white [&_span]:text-white [&_svg]:text-primary-500" />
        @endif

        <h1 class="max-w-3xl text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-primary-100 sm:text-base">{{ $subtitle }}</p>
        @endif
    </div>
</section>
