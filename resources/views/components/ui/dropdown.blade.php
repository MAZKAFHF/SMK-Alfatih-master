@props([
    'align' => 'right',
])

@php
    $alignments = [
        'left' => 'left-0 origin-top-left',
        'right' => 'right-0 origin-top-right',
        'center' => 'left-1/2 -translate-x-1/2 origin-top',
    ];
@endphp

<div data-dropdown class="relative inline-block" {{ $attributes }}>
    <div data-dropdown-toggle>
        {{ $trigger }}
    </div>

    <div
        data-dropdown-menu
        class="absolute z-50 mt-2 hidden min-w-44 rounded-xl border border-slate-200 bg-white p-1.5 shadow-pop dark:border-slate-700 dark:bg-slate-800 {{ $alignments[$align] }}"
        role="menu"
    >
        {{ $slot }}
    </div>
</div>
