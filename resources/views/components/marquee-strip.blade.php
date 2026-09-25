@props([
    'items' => [],
    'class' => '',
])

{{-- Strip berjalan label jurusan/nilai — pause saat hover, statis saat reduced-motion --}}
<div class="overflow-hidden {{ $class }}" role="presentation">
    <div class="marquee items-center gap-8 pr-8">
        @foreach ([$items, $items] as $group)
            @foreach ($group as $item)
                <span class="flex shrink-0 items-center gap-2.5 text-sm font-bold uppercase tracking-widest whitespace-nowrap">
                    <span class="size-1.5 rounded-full bg-gold-500" aria-hidden="true"></span>
                    {{ $item }}
                </span>
            @endforeach
        @endforeach
    </div>
</div>
