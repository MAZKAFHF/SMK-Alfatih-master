<x-layouts.app :title="'Login Admin'" :body-class="'bg-slate-50 dark:bg-slate-950'">
    <section class="flex min-h-[70vh] items-center justify-center px-4 py-16">
        <div class="w-full max-w-md">
            <x-ui.card class="p-6 sm:p-8">
                <div class="mb-8 flex flex-col items-center text-center">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo SMK Tahfizh Al-Fatih" width="72" height="72" class="size-18 rounded-2xl object-contain" />
                    <h1 class="mt-4 text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Masuk Admin</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Panel administrasi {{ config('app.name') }}</p>
                </div>

                @if ($errors->any())
                    <x-ui.alert variant="danger" title="Gagal masuk">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endif

                <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-6 space-y-5">
                    @csrf

                    <x-ui.input
                        label="Email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="admin@smkalfatih.sch.id"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <x-ui.input
                        label="Password"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-700" />
                            Ingat saya
                        </label>
                        <a href="{{ route('admin.password.request') }}" class="text-sm font-medium text-primary-700 hover:underline dark:text-primary-400">Lupa password?</a>
                    </div>

                    <x-ui.button type="submit" size="lg" full="true">Masuk</x-ui.button>
                </form>

                @php
                    $demoEmail = config('app.demo_admin_email');
                    $demoPassword = config('app.demo_admin_password');
                    $isProduction = app()->environment('production');
                @endphp

                @if ($demoEmail && $demoPassword && ! $isProduction)
                    <div class="mt-6">
                        <div class="relative" aria-hidden="true">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="bg-white px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:bg-slate-900 dark:text-slate-500">Akun Demo</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="email" value="{{ $demoEmail }}">
                            <input type="hidden" name="password" value="{{ $demoPassword }}">
                            <button
                                type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-lg border border-primary-200 bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition-colors hover:bg-primary-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 dark:border-primary-800 dark:bg-primary-950 dark:text-primary-400 dark:hover:bg-primary-900"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Masuk Sekali Klik sebagai Admin
                            </button>
                        </form>

                        <p class="mt-3 text-center text-xs text-slate-400 dark:text-slate-500">
                            Langsung masuk sebagai {{ $demoEmail }} tanpa mengetik kredensial.
                        </p>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </section>
</x-layouts.app>
