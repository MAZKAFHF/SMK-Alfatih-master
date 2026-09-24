<x-layouts.app :title="'Lupa Password'" :body-class="'bg-slate-50 dark:bg-slate-950'">
    <section class="flex min-h-[70vh] items-center justify-center px-4 py-16">
        <div class="w-full max-w-md">
            <x-ui.card class="p-6 sm:p-8">
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Lupa Password</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Masukkan email admin, kami akan kirim tautan reset.</p>

                @if(session('success'))<div class="mt-4"><x-ui.alert variant="success" title="Berhasil">{{ session('success') }}</x-ui.alert></div>@endif

                <form method="POST" action="{{ route('admin.password.email') }}" class="mt-6 space-y-5">
                    @csrf
                    <x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}" required autofocus />
                    <x-ui.button type="submit" size="lg" full="true">Kirim Tautan Reset</x-ui.button>
                </form>
                <p class="mt-4 text-center text-sm"><a href="{{ route('admin.login') }}" class="text-primary-700 hover:underline">Kembali ke Login</a></p>
            </x-ui.card>
        </div>
    </section>
</x-layouts.app>
