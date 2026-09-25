@props([
    'galleries' => [],
])

{{-- Mosaic dinamis: 1 besar + kecil + lebar — fallback grid bila item < 5 --}}
@if ($galleries->isNotEmpty())
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 md:grid-rows-2">
        @foreach ($galleries->take(6) as $gallery)
            @php
                $span = match($loop->index) {
                    0 => 'col-span-2 row-span-2',
                    4 => 'col-span-2',
                    default => '',
                };
            @endphp
            <a
                href="{{ route('gallery.index') }}"
                data-gallery-item
                data-category="{{ $gallery->category }}"
                data-title="{{ $gallery->title }}"
                data-src="{{ $gallery->image }}"
                class="group relative block overflow-hidden rounded-xl text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 {{ $span }} {{ $loop->index > 0 ? 'min-h-40' : 'min-h-80' }}"
                aria-label="Lihat foto: {{ $gallery->title }}"
            >
                @if ($gallery->image)
                    <img src="{{ $gallery->image }}" alt="{{ $gallery->title }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                @else
                    <span class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-primary-100 via-white to-accent-100 dark:from-primary-900 dark:via-slate-800 dark:to-accent-900" aria-hidden="true">
                        <svg class="size-10 text-primary-300 dark:text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        </svg>
                    </span>
                @endif
                <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-navy-950/80 to-transparent p-3 pt-8 opacity-0 transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100">
                    <span class="block truncate text-sm font-semibold text-white">{{ $gallery->title }}</span>
                </span>
            </a>
        @endforeach
    </div>
@endif
