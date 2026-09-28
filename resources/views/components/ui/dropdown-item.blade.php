@props([
    'href' => null,
    'active' => false,
    'danger' => false,
    'disabled' => false,
])

@php
    $style = $danger
        ? 'color: var(--ctl-danger);'
        : ($active ? 'color: var(--ctl-primary); font-weight: 600;' : 'color: var(--ctl-text);');
@endphp

@if ($href)
    <a href="{{ $href }}" data-dropdown-close role="menuitem" @if($disabled) aria-disabled="true" @endif style="{{ $style }}" {{ $attributes->except('class')->class(['ctl-option', '!rounded-lg', $attributes->get('class')]) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" data-dropdown-close role="menuitem" @disabled($disabled) style="{{ $style }}" {{ $attributes->merge(['class' => 'ctl-option !rounded-lg']) }}>
        {{ $slot }}
    </button>
@endif
