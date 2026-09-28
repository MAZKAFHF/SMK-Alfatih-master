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
                    :options="['' => 'Semua aktivitas'] + $eventOptions"
                    placeholder="Semua aktivitas"
                    :placeholder-option="false"
                >
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
                <div class="ctl-card overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3" style="border-bottom: 1px solid var(--ctl-border);">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-bold" style="background: var(--ctl-sunken); color: var(--ctl-muted);">
                            {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ $log->user?->name ?? 'Pengguna terhapus' }}</p>
                            <p class="ctl-muted truncate text-xs">{{ $log->user?->email ?? '—' }}</p>
                        </div>
                        <x-ui.badge :color="$log->event === \App\Models\LoginLog::EVENT_LOGIN ? 'green' : 'slate'" size="sm" dot>{{ $log->eventLabel() }}</x-ui.badge>
                    </div>

                    <dl class="space-y-2.5 px-4 py-3.5 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="ctl-faint shrink-0">IP Address</dt>
                            <dd class="text-right font-mono font-medium">{{ $log->ip_address ?? '—' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="ctl-faint shrink-0">Waktu</dt>
                            <dd class="text-right font-medium">{{ $log->created_at?->translatedFormat('d M Y, H:i:s') }}</dd>
                        </div>
                    </dl>

                    @if ($log->user_agent)
                        <div class="px-4 py-3" style="border-top: 1px solid var(--ctl-border);">
                            <p class="ctl-faint break-words text-xs leading-relaxed">{{ $log->user_agent }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden lg:block">
            <x-ui.table :head="['Pengguna', 'Aktivitas', 'IP Address', 'Perangkat', 'Waktu']">
                @foreach ($logs as $log)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-bold" style="background: var(--ctl-sunken); color: var(--ctl-muted);">
                                    {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold">{{ $log->user?->name ?? 'Pengguna terhapus' }}</p>
                                    <p class="ctl-muted truncate text-xs">{{ $log->user?->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-ui.badge :color="$log->event === \App\Models\LoginLog::EVENT_LOGIN ? 'green' : 'slate'" size="sm" dot>{{ $log->eventLabel() }}</x-ui.badge>
                        </td>
                        <td class="font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                        <td class="ctl-muted max-w-[280px] truncate text-xs">{{ $log->user_agent ?? '—' }}</td>
                        <td class="whitespace-nowrap">{{ $log->created_at?->translatedFormat('d M Y, H:i:s') }}</td>
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
