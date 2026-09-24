@props([
    'code' => '500',
    'title' => 'Terjadi Kesalahan',
    'message' => 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
])

<x-layouts.app :title="$code">
    <div class="flex min-h-[70vh] items-center justify-center px-4 py-16">
        <div class="mx-auto max-w-md text-center">
            <p class="text-7xl font-extrabold tracking-tight text-primary-600">{{ $code }}</p>
            <h1 class="mt-4 text-2xl font-bold text-slate-900">{{ $title }}</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-500">{{ $message }}</p>
            <div class="mt-8 flex items-center justify-center gap-3">
                <x-ui.button href="{{ route('home') }}">Kembali ke Beranda</x-ui.button>
                <x-ui.button variant="outline" onclick="history.back()">Kembali</x-ui.button>
            </div>
        </div>
    </div>
</x-layouts.app>
