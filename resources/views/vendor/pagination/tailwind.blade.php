@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-3">
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between sm:gap-3">
            <div>
                <p class="ctl-muted text-sm">
                    Menampilkan
                    @if ($paginator->firstItem())
                        <span class="font-semibold" style="color: var(--ctl-text);">{{ $paginator->firstItem() }}</span>
                        sampai
                        <span class="font-semibold" style="color: var(--ctl-text);">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    dari
                    <span class="font-semibold" style="color: var(--ctl-text);">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Halaman sebelumnya" class="ctl-btn ctl-btn-sm !px-2.5" style="opacity: .45; cursor: not-allowed; background: var(--ctl-surface); border: 1px solid var(--ctl-border); color: var(--ctl-faint);">
                            <x-ui.chevron-left class="size-4" />
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="ctl-btn ctl-btn-ghost ctl-btn-sm !px-2.5" style="border: 1px solid var(--ctl-border);">
                            <x-ui.chevron-left class="size-4" />
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="ctl-faint relative inline-flex items-center px-2 py-2 text-sm">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="ctl-btn ctl-btn-primary ctl-btn-sm !px-3.5">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="ctl-btn ctl-btn-ghost ctl-btn-sm !px-3.5" style="border: 1px solid var(--ctl-border);">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" class="ctl-btn ctl-btn-ghost ctl-btn-sm !px-2.5" style="border: 1px solid var(--ctl-border);">
                            <x-ui.chevron-right class="size-4" />
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Halaman berikutnya" class="ctl-btn ctl-btn-sm !px-2.5" style="opacity: .45; cursor: not-allowed; background: var(--ctl-surface); border: 1px solid var(--ctl-border); color: var(--ctl-faint);">
                            <x-ui.chevron-right class="size-4" />
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
