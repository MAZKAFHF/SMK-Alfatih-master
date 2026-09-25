<x-layouts.app :title="'Program Keahlian'">
    <x-page-header
        title="Program Keahlian"
        subtitle="Pilih kompetensi yang sesuai dengan minat dan bakatmu. Semua program dirancang untuk membekali siswa dengan keterampilan siap kerja."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Program Keahlian'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if ($programs->isEmpty())
                <x-ui.empty-state title="Belum ada program keahlian" description="Program keahlian akan segera diumumkan." />
            @endif

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($programs as $program)
                    <div class="reveal" style="--reveal-delay: {{ $loop->index * 80 }}ms">
                        <x-program-card :program="$program" :tone="['emerald', 'orange', 'gold', 'navy'][$loop->index % 4]" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
