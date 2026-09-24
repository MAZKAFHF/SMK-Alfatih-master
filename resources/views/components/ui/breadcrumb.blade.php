@props(['items' => []])

<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-sm">
        @foreach ($items as $label => $url)
            @if (is_int($label))
                @php
                    $label = $url['label'] ?? '…';
                    $url = $url['url'] ?? null;
                @endphp
            @endif

            <li class="flex items-center gap-1.5">
                @if ($url)
                    <a href="{{ $url }}" class="text-slate-500 transition-colors hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400">
                        {{ $label }}
                    </a>
                    <svg class="size-3.5 text-slate-300 dark:text-slate-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                @else
                    <span class="font-medium text-slate-900 dark:text-white" aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
