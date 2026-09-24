<x-admin.layouts.app :title="'Log Login'">
    @php
        $eventOptions = [
            \App\Models\LoginLog::EVENT_LOGIN => 'Masuk',
            \App\Models\LoginLog::EVENT_LOGOUT => 'Keluar',
        ];
    @endphp

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Total <strong class="font-semibold text-slate-900 dark:text-white">{{ number_format($logs->total()) }}</strong> aktivitas login
        </p>
        <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            Hanya superadmin
        </span>
    </div>

    <x-ui.card class="mb-6 p-4">
        <form method="GET" action="{{ route('admin.login-logs.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <x-ui.input
                    name="search"
                    placeholder="Cari nama atau email pengguna..."
                    value="{{ request('search') }}"
                />
            </div>

            <div class="sm:w-44">
                <x-ui.select
                    name="event"
                    :value="request('event')"
                    :options="$eventOptions"
                    :placeholder-option="false"
                >
                    <option value="" {{ blank(request('event')) ? 'selected' : '' }}>Semua aktivitas</option>
                </x-ui.select>
            </div>

            <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>

            @if (request()->has('search') || request()->has('event'))
                <x-ui.button variant="ghost" href="{{ route('admin.login-logs.index') }}">Reset</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    @if ($logs->isEmpty())
        <x-ui.card class="p-6">
            <x-ui.empty-state
                title="Belum ada aktivitas"
                description="Log login akan tercatat setiap admin masuk atau keluar."
            />
        </x-ui.card>
    @else
        {{-- Mobile: kartu --}}
        <div class="space-y-4 lg:hidden">
            @foreach ($logs as $log)
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-700/50">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                            {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $log->user?->name ?? 'Pengguna terhapus' }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $log->user?->email ?? '—' }}</p>
                        </div>
                        <x-ui.badge :color="$log->event === \App\Models\LoginLog::EVENT_LOGIN ? 'green' : 'slate'" size="sm" dot>{{ $log->eventLabel() }}</x-ui.badge>
                    </div>

                    <dl class="space-y-2.5 px-4 py-3.5 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="shrink-0 text-slate-400 dark:text-slate-500">IP Address</dt>
                            <dd class="font-mono text-right font-medium text-slate-700 dark:text-slate-300">{{ $log->ip_address ?? '—' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="shrink-0 text-slate-400 dark:text-slate-500">Waktu</dt>
                            <dd class="text-right font-medium text-slate-700 dark:text-slate-300">{{ $log->created_at?->translatedFormat('d M Y, H:i:s') }}</dd>
                        </div>
                    </dl>

                    @if ($log->user_agent)
                        <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-700/50">
                            <p class="break-words text-xs leading-relaxed text-slate-400 dark:text-slate-500">{{ $log->user_agent }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden lg:block">
            <x-ui.table :head="['Pengguna', 'Aktivitas', 'IP Address', 'Perangkat', 'Waktu']">
                @foreach ($logs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $log->user?->name ?? 'Pengguna terhapus' }}</p>
                                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $log->user?->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge :color="$log->event === \App\Models\LoginLog::EVENT_LOGIN ? 'green' : 'slate'" size="sm" dot>{{ $log->eventLabel() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $log->ip_address ?? '—' }}</td>
                        <td class="max-w-[280px] truncate px-4 py-3 text-xs text-slate-500 dark:text-slate-400" title="{{ $log->user_agent }}">{{ $log->user_agent ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">{{ $log->created_at?->translatedFormat('d M Y, H:i:s') }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </div>
    @endif

    @if ($logs->hasPages())
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @endif
</x-admin.layouts.app>
