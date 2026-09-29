<x-portal.auth-frame title="Reset Password">
<section><x-ui.card class="p-6">
<h1 class="text-xl font-extrabold">Reset Password</h1>
<form method="POST" novalidate action="{{ route('portal.password.update') }}" class="mt-5 space-y-4">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<x-ui.input label="Email" name="email" type="email" value="{{ old('email', $email) }}" required />
<x-ui.input label="Password baru" name="password" type="password" required />
<x-ui.input label="Konfirmasi" name="password_confirmation" type="password" required />
<x-ui.button type="submit" full="true">Reset Password</x-ui.button>
</form></x-ui.card></section>
</x-portal.auth-frame>
