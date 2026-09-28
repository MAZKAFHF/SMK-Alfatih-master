@props([
    'items' => [],
    'class' => '',
])

{{-- Strip berjalan label jurusan/nilai — seamless infinite, pause saat hover, statis saat reduced-motion --}}
{{-- Pola: 2 grup identik, tiap grup w-max shrink-0, spacing via padding simetris (bukan gap) agar -50% eksak --}}
<div class="marquee-viewport overflow-hidden {{ $class }}" role="marquee" aria-label="{{ implode(', ', $items) }}">
    <div class="marquee">
        @foreach ([false, true] as $isDuplicate)
            <div class="marquee-group" @if($isDuplicate) aria-hidden="true" @endif>
                {{-- Ulangi item 3x per grup agar track selalu > 2x viewport (aman ultrawide) --}}
                @foreach ([0, 1, 2] as $repeat)
                    @foreach ($items as $item)
                        <span class="marquee-item">
                            <span class="size-1.5 shrink-0 rounded-full bg-gold-500" aria-hidden="true"></span>
                            <span class="whitespace-nowrap">{{ $item }}</span>
                        </span>
                    @endforeach
                @endforeach
            </div>
        @endforeach
    </div>
</div>
