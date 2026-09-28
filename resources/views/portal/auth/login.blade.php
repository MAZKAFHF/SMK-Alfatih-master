<x-layouts.app :title="'Masuk Portal PPDB'">
<section class="mx-auto max-w-md px-4 py-14">
<x-ui.card class="p-6 sm:p-8">
<h1 class="font-display text-xl font-extrabold">Masuk Portal PPDB</h1>
<p class="mt-1 text-sm text-slate-500">Kelola pendaftaran semua anak dalam satu akun.</p>
@auth
@if(auth()->user()->is_admin)
<div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-amber-800">Anda sedang masuk sebagai <strong>admin</strong> ({{ auth()->user()->email }}). Masuk di sini akan <strong>beralih ke akun pemohon</strong>. Panel admin ada di <a href="{{ route('admin.dashboard') }}" class="font-semibold underline">Control Center</a>.</div>
@endif
@endauth
<form method="POST" novalidate action="{{ route('portal.login.store') }}" class="mt-6 space-y-5">@csrf
<x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
<x-ui.input label="Password" name="password" type="password" required autocomplete="current-password" />
<x-ui.checkbox label="Ingat saya" name="remember" value="1" />
<x-ui.validation-summary title="Gagal masuk" />
<x-ui.button type="submit" size="lg" full="true">Masuk</x-ui.button>
<div class="flex justify-between text-sm"><a href="{{ route('portal.register') }}" class="font-semibold text-primary-700">Buat akun</a><a href="{{ route('portal.password.request') }}" class="text-slate-500">Lupa password?</a></div>
</form>
</x-ui.card>
</section>
</x-layouts.app>
