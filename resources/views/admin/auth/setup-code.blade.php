<x-admin.layouts.app :title="'Buat Kode Admin'">
    <div class="mx-auto max-w-lg py-8">
        <x-ui.card class="p-6 sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-primary-700 dark:text-primary-400">Keamanan akun</p>
            <h1 class="mt-2 text-2xl font-extrabold">Buat kode admin 4 digit</h1>
            <p class="ctl-muted mt-3 text-sm leading-relaxed">Kode ini diperlukan untuk tindakan sensitif seperti menghapus data pendaftar, akun admin, dan membersihkan log. Kode disimpan secara terenkripsi dan tidak dapat dilihat kembali.</p>

            <form method="POST" action="{{ route('admin.code.store') }}" class="mt-6 space-y-4">
                @csrf
                <x-ui.input label="Kode admin" name="admin_code" type="password" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="new-password" required />
                <x-ui.input label="Ulangi kode admin" name="admin_code_confirmation" type="password" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="new-password" required />
                <x-ui.button type="submit" full="true">Simpan dan Masuk Dashboard</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-admin.layouts.app>
