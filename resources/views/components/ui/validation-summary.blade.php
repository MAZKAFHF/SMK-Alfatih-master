@props([
    'title' => 'Periksa kembali',
])

{{--
    Ringkasan error validasi anti-duplikasi:
    - 0 error : tidak render apa pun
    - 1 error : tidak render (cukup error di bawah field, hindari noise ganda)
    - 2+ error: ringkasan count-aware + daftar pesan unik + tautan ke field
    Pesan berasal dari lang/id/validation.php (Bahasa Indonesia).
--}}
@php
    // Satu pesan pertama per field, lalu buang duplikat persis.
    $items = collect($errors->getMessages())
        ->map(fn ($msgs, $field) => ['field' => $field, 'message' => $msgs[0] ?? ''])
        ->values()
        ->unique('message')
        ->values();
@endphp

@if ($items->count() > 1)
    <div role="alert" aria-live="assertive" data-validation-summary tabindex="-1" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
        <p class="font-bold">{{ $title }}</p>
        <p class="mt-0.5">Ada {{ $items->count() }} bagian yang perlu diperbaiki sebelum melanjutkan.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($items as $item)
                <li>
                    <a href="#{{ $item['field'] }}" class="underline decoration-red-300 underline-offset-2 hover:decoration-red-500">{{ $item['message'] }}</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
