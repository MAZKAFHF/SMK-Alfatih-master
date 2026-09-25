@props([
    'steps' => ['Learn', 'Build', 'Impact'],
    'class' => '',
])

{{-- Digital Pulse — garis progresi signature, JANGAN dipakai di form/tabel/toast --}}
<div class="pulse-track {{ $class }}" aria-hidden="true">
    <div class="h-px w-full bg-current opacity-25"></div>
    <span class="pulse-dot block size-2.5 rounded-full bg-gold-500 shadow-[0_0_12px_2px_rgb(215_168_62/0.7)]"></span>
    <div class="flex justify-between pt-3">
        @foreach ($steps as $step)
            <span class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-widest opacity-70">
                <span class="size-1.5 rounded-full bg-current"></span>
                {{ $step }}
            </span>
        @endforeach
    </div>
</div>
