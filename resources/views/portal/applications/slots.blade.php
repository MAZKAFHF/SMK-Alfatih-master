<x-portal.layouts.app :title="'Pilih Jadwal — '.$application->name">
<h1 class="font-display text-xl font-extrabold">Pilih Jadwal Wawancara</h1>
<p class="text-sm text-slate-500">{{ $application->name }} — {{ $application->registration_number }}</p>
@if($slots->isEmpty())
<div class="mt-4"><x-ui.alert variant="info" title="Belum ada jadwal">Belum ada jadwal wawancara yang tersedia saat ini. Silakan periksa kembali atau hubungi Admin PPDB.</x-ui.alert></div>
@else
<form method="POST" novalidate action="{{ route('portal.slots.book', $application) }}" class="mt-4 space-y-3">@csrf
@foreach($slots->groupBy(fn($s) => $s->date->format('l, d M Y')) as $day => $daySlots)
<x-ui.card class="p-4"><h2 class="font-bold">{{ $day }}</h2><div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
@foreach($daySlots as $slot)
<label class="flex cursor-pointer items-center justify-center rounded-xl border px-3 py-3 text-sm font-bold {{ $slot->remaining() > 0 ? 'hover:border-primary-500' : 'opacity-40' }}">
<input type="radio" name="slot_id" value="{{ $slot->id }}" class="sr-only" {{ $slot->remaining() <= 0 ? 'disabled' : '' }}>
<span>{{ $slot->start_time }}<br><span class="text-xs font-medium text-slate-500">{{ $slot->location ?? '' }} (sisa {{ $slot->remaining() }})</span></span>
</label>
@endforeach
</div></x-ui.card>
@endforeach
<x-ui.validation-summary title="Gagal booking" />
<x-ui.button type="submit">Konfirmasi Pilihan Slot</x-ui.button>
</form>
@endif
</x-portal.layouts.app>
