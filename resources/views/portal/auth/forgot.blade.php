<x-portal.auth-frame title="Lupa Password">
<section><x-ui.card class="p-6">
<h1 class="text-xl font-extrabold">Lupa Password</h1>
<form method="POST" novalidate action="{{ route('portal.password.email') }}" class="mt-5 space-y-4">@csrf
<x-ui.input label="Email akun" name="email" type="email" required />
<x-ui.button type="submit" full="true">Kirim Link Reset</x-ui.button>
</form></x-ui.card></section>
</x-portal.auth-frame>
