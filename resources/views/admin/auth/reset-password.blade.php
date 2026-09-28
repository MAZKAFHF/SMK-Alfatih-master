<x-layouts.app :title="'Reset Password'" :body-class="'bg-slate-50 dark:bg-slate-950'">
    <section class="flex min-h-[70vh] items-center justify-center px-4 py-16">
        <div class="w-full max-w-md">
            <x-ui.card class="p-6 sm:p-8">
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Reset Password</h1>
                <div class="mt-4"><x-ui.validation-summary title="Gagal" /></div>
                <form method="POST" novalidate action="{{ route('admin.password.update') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <x-ui.input label="Email" name="email" type="email" value="{{ old('email',$email) }}" required />
                    <x-ui.input label="Password Baru" name="password" type="password" required />
                    <x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" required />
                    <x-ui.button type="submit" size="lg" full="true">Reset Password</x-ui.button>
                </form>
            </x-ui.card>
        </div>
    </section>
</x-layouts.app>
