@props([
    'id' => null,
    'tabs' => [],
])

@php
    $id = $id ?? 'tabs-' . Str::random(6);
@endphp

<div data-tabs="{{ $id }}" {{ $attributes }}>
    <div class="flex flex-wrap gap-1 border-b border-slate-200 dark:border-slate-700" role="tablist">
        @foreach ($tabs as $label => $tabId)
            <button
                type="button"
                data-tab-trigger
                data-target="#{{ $tabId }}"
                role="tab"
                class="-mb-px border-b-2 px-4 py-2.5 text-sm font-medium transition-colors data-[active=true]:border-primary-600 data-[active=true]:text-primary-700 data-[active=false]:border-transparent data-[active=false]:text-slate-500 data-[active=false]:hover:border-slate-300 data-[active=false]:hover:text-slate-800 dark:data-[active=true]:text-primary-400 dark:data-[active=false]:text-slate-400 dark:data-[active=false]:hover:border-slate-600 dark:data-[active=false]:hover:text-slate-300"
                data-active="false"
            >{{ $label }}</button>
        @endforeach
    </div>

    <div class="pt-5">
        {{ $slot }}
    </div>
</div>
