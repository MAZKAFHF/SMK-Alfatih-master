@props([
    'id' => null,
    'tabs' => [],
])

@php
    $id = $id ?? 'tabs-' . Str::random(6);
@endphp

<div data-tabs="{{ $id }}" {{ $attributes }}>
    <div class="ctl-tabs" role="tablist" aria-label="Navigasi tab">
        @foreach ($tabs as $label => $tabId)
            <button
                type="button"
                data-tab-trigger
                data-target="#{{ $tabId }}"
                role="tab"
                aria-selected="false"
                aria-controls="{{ $tabId }}"
                class="ctl-tab"
                data-active="false"
            >{{ $label }}</button>
        @endforeach
    </div>

    <div class="pt-5">
        {{ $slot }}
    </div>
</div>
