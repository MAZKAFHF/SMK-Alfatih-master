@props([
    'stats' => [],
])

{{-- Pita statistik editorial dengan counter animasi sekali-putar --}}
<dl class="grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-slate-200 bg-slate-200 dark:border-slate-800 dark:bg-slate-800 lg:grid-cols-4">
    @foreach ($stats as $stat)
        @if(!blank($stat['value']))
            <div class="reveal bg-white px-6 py-7 text-center dark:bg-slate-900" style="--reveal-delay: {{ $loop->index * 80 }}ms">
                <dd class="font-display text-3xl font-extrabold tracking-tight text-forest-800 dark:text-tech-400 sm:text-4xl">
                    <span data-counter data-target="{{ preg_replace('/[^0-9]/', '', $stat['value']) }}" data-suffix="{{ preg_replace('/[0-9]/', '', $stat['value']) }}">0</span>
                </dd>
                <dt class="mt-1.5 text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</dt>
            </div>
        @endif
    @endforeach
</dl>
