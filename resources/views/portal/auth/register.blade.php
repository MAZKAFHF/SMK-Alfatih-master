<x-layouts.app :title="'Buat Akun PPDB'">
<section class="mx-auto max-w-md px-4 py-14">
<x-ui.card class="p-6 sm:p-8">
<h1 class="font-display text-xl font-extrabold">Buat Akun PPDB</h1>
<p class="mt-1 text-sm text-slate-500">Satu akun untuk mendaftarkan banyak anak. Email + password.</p>
@auth
@if(auth()->user()->is_admin)
<div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-amber-800">Anda sedang masuk sebagai <strong>admin</strong> ({{ auth()->user()->email }}). Membuat akun pemohon akan <strong>mengeluarkan sesi admin</strong> dan masuk sebagai akun baru. Untuk kembali ke admin, masuk lagi via <a href="{{ route('admin.login') }}" class="font-semibold underline">/admin/login</a>.</div>
@endif
@endauth
<form method="POST" novalidate action="{{ route('portal.register.store') }}" class="mt-6 space-y-5">@csrf
<x-ui.input label="Nama Orang Tua / Wali" name="name" value="{{ old('name') }}" required autofocus />
<x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}" required />
<x-ui.input label="No. HP / WhatsApp" name="phone" value="{{ old('phone') }}" type="tel" />
<x-ui.input label="Password (min 8, huruf+angka)" name="password" type="password" required autocomplete="new-password" />
<x-ui.input label="Konfirmasi Password" name="password_confirmation" type="password" required />
<x-ui.validation-summary />
<x-ui.button type="submit" size="lg" full="true">Buat Akun</x-ui.button>
<p class="text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('portal.login') }}" class="font-semibold text-primary-700">Masuk</a></p>
</form>
</x-ui.card>
</section>
</x-layouts.app>
