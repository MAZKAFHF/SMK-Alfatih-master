@props([
    // cinematic | editorial | manifesto | portrait | media | tech | visual | info | minimal | story
    'variant' => 'info',
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'breadcrumbs' => [],
    // Video-ready contract: pass $video = ['src' => ..., 'poster' => ...] later
    // to swap the media slot to <video> with ZERO structural change.
    'video' => null,
    'media' => null, // slot name for custom media panel
])

@php
    // Keep text contrast aligned with the actual wash used by each variant.
    $dark = in_array($variant, ['cinematic', 'manifesto', 'media', 'tech', 'visual', 'info'], true);
    $washes = [
        'cinematic' => 'wash-navy',
        'editorial' => 'wash-editorial',
        'manifesto' => 'wash-navy',
        'portrait' => 'wash-offwhite',
        'media' => 'wash-navy',
        'tech' => 'wash-navy',
        'visual' => 'wash-navy',
        'info' => 'wash-navy',
        'minimal' => 'wash-editorial',
        'story' => 'wash-greentint',
    ];
    $wash = $washes[$variant] ?? 'wash-navy';
    $split = in_array($variant, ['cinematic', 'tech', 'portrait', 'media'], true);
@endphp

<section class="relative overflow-hidden {{ $wash }}" data-hero>
    @if($dark)
        <div class="tech-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <x-motif-geometric class="absolute -right-10 -top-10 size-52 text-tech-500/15" />
    @else
        <div class="tech-grid-light pointer-events-none absolute inset-0 dark:hidden" aria-hidden="true"></div>
    @endif
    @if(in_array($variant, ['cinematic', 'manifesto']))
        <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-primary-600 via-gold-500 to-energy-500" aria-hidden="true"></div>
    @endif

    <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 {{ $variant === 'cinematic' ? 'lg:py-24' : 'lg:py-16' }} {{ $split ? 'grid items-center gap-10 lg:grid-cols-2' : '' }}">
        <div>
            @if(count($breadcrumbs) > 0)
                <div data-hero-item style="--hero-delay: 0ms">
                    <x-ui.breadcrumb :items="$breadcrumbs" class="{{ $dark ? '[&_a]:text-slate-300 [&_a:hover]:text-gold-400 [&_span]:text-white [&_svg]:text-gold-500' : '' }}" />
                </div>
            @endif
            @if($eyebrow)
                <p class="mt-4 text-xs font-bold uppercase tracking-[0.25em] {{ $dark ? 'text-gold-400' : 'text-energy-600 dark:text-energy-500' }}" data-hero-item style="--hero-delay: 60ms">{{ $eyebrow }}</p>
            @endif
            @if($title)
                <h1 class="mt-3 max-w-3xl font-display text-3xl font-extrabold tracking-tight {{ $dark ? 'text-white' : 'text-slate-900 dark:text-white' }} {{ $variant === 'cinematic' ? 'sm:text-5xl' : 'sm:text-4xl' }}" data-mask-group>
                    <span data-mask-line><span>{{ $title }}</span></span>
                </h1>
            @endif
            @if($variant === 'manifesto')
                <span class="mt-5 block h-1 w-20 rounded-full bg-gold-500" data-draw-line aria-hidden="true"></span>
            @endif
            @if($description)
                <p class="mt-4 max-w-2xl leading-relaxed {{ $dark ? 'text-slate-300' : 'text-slate-600 dark:text-slate-400' }} {{ $variant === 'cinematic' ? 'text-lg' : 'text-sm sm:text-base' }}" data-hero-item style="--hero-delay: 200ms">{{ $description }}</p>
            @endif
            @if(trim($slot))
                <div class="mt-6" data-hero-item style="--hero-delay: 280ms">{{ $slot }}</div>
            @endif
        </div>
        @if($split)
            <div data-hero-item style="--hero-delay: 220ms">
                @if($video)
                    {{-- VIDEO DROP-IN: same frame, no structural change (Phase: footage pending) --}}
                    <div data-media class="overflow-hidden rounded-2xl shadow-soft">
                        <video src="{{ $video['src'] }}" poster="{{ $video['poster'] ?? '' }}" muted loop playsinline preload="metadata" class="aspect-video w-full object-cover"></video>
                    </div>
                @elseif(!empty($media ?? null))
                    {{ $media }}
                @endif
            </div>
        @endif
    </div>
</section>
