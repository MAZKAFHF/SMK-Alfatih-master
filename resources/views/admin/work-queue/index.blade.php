<x-admin.layouts.app :title="'Antrean Kerja'">
    <x-admin.page-head title="Antrean Kerja" context="Tugas operasional yang masih membutuhkan tindakan admin." />

    @php
        $sections = [
            ['Verifikasi pendaftar', $applications, fn($item) => route('admin.registrations.show', $item), fn($item) => $item->registration_number.' — '.$item->name],
            ['Permintaan pindah jadwal', $reschedules, fn($item) => route('admin.slots.index'), fn($item) => ($item->appointment?->application?->registration_number ?? 'Pendaftar').' — '.$item->reason],
            ['Wawancara jatuh tempo', $interviews, fn($item) => route('admin.slots.index'), fn($item) => ($item->application?->registration_number ?? 'Pendaftar').' — '.($item->slot?->date?->translatedFormat('d M Y') ?? '-')],
            ['Pesan belum selesai', $messages, fn($item) => route('admin.contact-messages.show', $item), fn($item) => $item->name.' — '.$item->subject],
            ['Email gagal', $failedEmails, fn($item) => $item->application ? route('admin.registrations.show', $item->application) : route('admin.dashboard'), fn($item) => $item->recipient.' — '.$item->subject],
        ];
    @endphp

    <div class="grid gap-5 lg:grid-cols-2">
        @foreach($sections as [$title, $items, $url, $label])
            <x-ui.card class="p-5">
                <div class="flex items-center justify-between"><h2 class="font-bold">{{ $title }}</h2><x-ui.badge color="{{ $items->isEmpty() ? 'green' : 'amber' }}">{{ $items->count() }}</x-ui.badge></div>
                @if($items->isEmpty())
                    <p class="ctl-muted mt-4 text-sm">Tidak ada tugas pada kategori ini.</p>
                @else
                    <div class="mt-3 divide-y" style="border-color: var(--ctl-border)">
                        @foreach($items as $item)
                            <a href="{{ $url($item) }}" class="block py-3 text-sm hover:text-primary-600">{{ $label($item) }}</a>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        @endforeach
    </div>
</x-admin.layouts.app>
