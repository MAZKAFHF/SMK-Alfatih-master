<x-layouts.app :title="'Lupa Password'">
<section class="mx-auto max-w-md px-4 py-14"><x-ui.card class="p-6">
<h1 class="text-xl font-extrabold">Lupa Password</h1>
<form method="POST" novalidate action="{{ route('portal.password.email') }}" class="mt-5 space-y-4">@csrf
<x-ui.input label="Email akun" name="email" type="email" required />
<x-ui.button type="submit" full="true">Kirim Link Reset</x-ui.button>
</form></x-ui.card></section>
</x-layouts.app>
