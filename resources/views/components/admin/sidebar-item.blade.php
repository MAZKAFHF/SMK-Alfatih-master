@props([
    'href' => '#',
    'icon' => 'dashboard',
    'label' => '',
    'active' => false,
    'badge' => null,
])

{{-- Item navigasi sidebar ALFATIH//CONTROL — SATU-SATUNYA cara membuat menu.
     Tinggi 44px seragam, indikator slide milik bersama, light/dark via token. --}}
<a
    href="{{ $href }}"
    class="ctl-navitem"
    @if($active) aria-current="page" @endif
    data-side-item
>
    <span class="flex min-w-0 items-center gap-3">
        <span class="ctl-navicon"><x-admin.icon :name="$icon" /></span>
        <span class="truncate">{{ $label }}</span>
    </span>
    @if(!empty($badge) && $badge > 0)
        <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-energy-500 text-[11px] font-bold text-white">{{ $badge > 99 ? '99+' : $badge }}</span>
    @endif
</a>
