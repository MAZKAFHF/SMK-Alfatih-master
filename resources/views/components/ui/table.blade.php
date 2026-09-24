@props([
    'head' => [],
    'empty' => null,
    'striped' => false,
])

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700']) }}>
        @if (count($head) > 0)
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    @foreach ($head as $heading)
                        <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
            {{ $slot }}
        </tbody>
    </table>

    @if ($empty)
        {{ $empty }}
    @endif
</div>
