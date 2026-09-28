@props([
    'title' => null,
    'description' => null,
])

<div class="ctl-card !p-6">
    @if($title)
        <h3 class="text-sm font-semibold" style="color: var(--ctl-text);">{{ $title }}</h3>
        @if($description)
            <p class="ctl-muted mt-1 text-xs">{{ $description }}</p>
        @endif
        <div class="mt-4 space-y-5">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</div>
