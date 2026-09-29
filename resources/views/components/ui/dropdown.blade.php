@props([
    'align' => 'right',
    'panelClass' => '',
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
        class="ctl-popover absolute z-50 mt-2 hidden min-w-44 p-1.5 {{ $alignments[$align] }} {{ $panelClass }}"
        role="menu"
    >
        {{ $slot }}
    </div>
</div>
