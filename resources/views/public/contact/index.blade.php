<x-layouts.app :title="'Kontak'">
    <x-page-header
        title="Hubungi Kami"
        subtitle="Kami siap membantu. Sampaikan pertanyaan atau keperluan Anda melalui form di bawah ini."
        :breadcrumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Kontak'],
        ]"
    />

    <section class="py-12 lg:py-16">
        <div class="reveal mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-5">
                {{-- Info --}}
                <div class="space-y-4 lg:col-span-2">
                    @php
                        $contactAddress = \App\Models\SiteSetting::get('school_address', 'Jl. Pendidikan No. 1, Kec. Jakarta Pusat, DKI Jakarta');
                        $contactPhone = \App\Models\SiteSetting::get('school_phone', '(021) 1234-5678');
                        $contactEmail = \App\Models\SiteSetting::get('school_email', 'info@smkalfatih.sch.id');
                    @endphp
                    @foreach ([
                        ['label' => 'Alamat', 'value' => $contactAddress, 'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
                        ['label' => 'Telepon / WA', 'value' => $contactPhone, 'icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z'],
                        ['label' => 'Email', 'value' => $contactEmail, 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75'],
                        ['label' => 'Jam Layanan', 'value' => 'Senin – Jumat, 07.00 – 15.00 WIB', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ] as $item)
                        <div class="flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
                            <span class="mt-0.5 flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400" aria-hidden="true">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item['label'] }}</h2>
                                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $item['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Form --}}
                <div class="lg:col-span-3">
                    <x-ui.card class="h-full">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Kirim Pesan</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Isi form di bawah, kami akan merespons secepatnya.</p>

                        <form method="POST" action="{{ route('contact.send') }}" class="mt-6 space-y-5" novalidate>
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <x-ui.input label="Nama Lengkap" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required autofocus />
                                <x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}" placeholder="email@contoh.com" required />
                                <x-ui.input label="No. HP / WhatsApp" name="phone" type="tel" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" />
                                <x-ui.input label="Subjek" name="subject" value="{{ old('subject') }}" placeholder="Judul pesan" required />
                            </div>

                            <x-ui.textarea label="Pesan" name="message" rows="6" placeholder="Tulis pesan Anda di sini..." required>{{ old('message') }}</x-ui.textarea>

                            <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-slate-400 dark:text-slate-500">Pesan Anda akan kami balas melalui email atau WhatsApp.</p>
                                <x-ui.button type="submit" size="lg">Kirim Pesan</x-ui.button>
                            </div>
                        </form>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
