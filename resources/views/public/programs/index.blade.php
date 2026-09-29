<x-layouts.app :title="'Program Keahlian'">
    <x-page-hero
        variant="tech"
        eyebrow="SMK Teknologi"
        title="Belajar untuk Membangun."
        description="Kenali setiap program keahlian dan pilih bidang belajar yang paling sesuai dengan minat serta bakatmu."
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

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-stagger>
                @foreach ($programs as $program)
                    <div data-stagger-item>
                        <x-program-card :program="$program" :tone="['emerald', 'orange', 'gold', 'navy'][$loop->index % 4]" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
