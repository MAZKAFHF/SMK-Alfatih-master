@props([
    'program' => null,
    'tone' => 'emerald',
])

@php
    $tones = [
        'emerald' => ['bar' => 'bg-primary-600', 'text' => 'group-hover:text-primary-700 dark:group-hover:text-primary-400', 'ring' => 'group-hover:ring-primary-200'],
        'orange' => ['bar' => 'bg-energy-500', 'text' => 'group-hover:text-energy-600', 'ring' => 'group-hover:ring-energy-500/30'],
        'gold' => ['bar' => 'bg-gold-500', 'text' => 'group-hover:text-gold-600', 'ring' => 'group-hover:ring-gold-500/30'],
        'navy' => ['bar' => 'bg-navy-800', 'text' => 'group-hover:text-navy-800 dark:group-hover:text-white', 'ring' => 'group-hover:ring-navy-800/20'],
    ];
    $t = $tones[$tone] ?? $tones['emerald'];
@endphp

<a href="{{ route('programs.show', $program) }}" class="group block h-full">
    <x-ui.card padding="false" hover="true" class="clip-corner-sm h-full overflow-hidden ring-1 ring-transparent transition {{ $t['ring'] }}">
        <x-thumb :src="$program->image" ratio="aspect-[4/3]" alt="{{ $program->name }}" />
        <div class="p-5">
            <span class="block h-1 w-10 rounded-full {{ $t['bar'] }}" aria-hidden="true"></span>
            <h3 class="mt-3 text-lg font-bold text-slate-900 transition-colors dark:text-white {{ $t['text'] }}">{{ $program->name }}</h3>
            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $program->short_description }}</p>
            <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-slate-700 dark:text-slate-300">
                Selengkapnya
                <svg class="size-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                </svg>
            </span>
        </div>
    </x-ui.card>
</a>
