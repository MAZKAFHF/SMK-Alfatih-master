<x-admin.layouts.app :title="'Kelola User'">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Kelola User</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Tambahkan akun admin baru atau perbarui data &amp; password akun yang ada.</p>
        </div>

        <x-ui.button variant="primary" size="md" onclick="openModal('create-user-modal')">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Admin
        </x-ui.button>
    </div>

    <x-ui.table :head="['Nama', 'Email', 'Role', 'Terdaftar', 'Aksi']">
        @foreach ($users as $user)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-900 dark:text-primary-400">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $user->email }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :color="$user->is_superadmin ? 'purple' : 'blue'" size="sm" dot>
                        {{ $user->is_superadmin ? 'Super Admin' : 'Admin' }}
                    </x-ui.badge>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                <td class="px-4 py-3 text-right">
                    <x-ui.button
                        variant="outline"
                        size="sm"
                        data-edit-user
                        data-url="{{ route('admin.users.update', $user) }}"
                        data-name="{{ $user->name }}"
                        data-email="{{ $user->email }}"
                        data-role="{{ $user->is_superadmin ? 'superadmin' : 'admin' }}"
                        data-self="{{ $user->is(auth()->user()) ? '1' : '0' }}"
                        onclick="fillEditModal(this)"
                    >Edit</x-ui.button>
                </td>
            </tr>
        @endforeach
    </x-ui.table>

    @if ($users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    @endif

    {{-- Modal: tambah admin --}}
    <x-ui.modal id="create-user-modal" title="Tambah Akun Admin">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <x-ui.input label="Nama Lengkap" name="name" value="{{ old('name') }}" placeholder="Nama admin" required />
            <x-ui.input label="Email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@smkalfatih.sch.id" required />

            <x-ui.input
                label="Password"
                name="password"
                type="password"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                required
            />

            <x-ui.select
                label="Role"
                name="role"
                :value="old('role', 'admin')"
                :options="['admin' => 'Admin', 'superadmin' => 'Super Admin']"
                :placeholder-option="false"
                required
            />

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" type="button" data-modal-close>Batal</x-ui.button>
                <x-ui.button variant="primary" type="submit">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal: edit user --}}
    <x-ui.modal id="edit-user-modal" title="Edit Akun">
        <form id="edit-user-form" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <x-ui.input label="Nama Lengkap" name="name" id="edit-user-name" value="" required />
            <x-ui.input label="Email" name="email" type="email" id="edit-user-email" value="" required />

            <x-ui.input
                label="Password Baru"
                name="password"
                type="password"
                id="edit-user-password"
                placeholder="Biarkan kosong jika tidak ingin mengubah"
                autocomplete="new-password"
            />

            <div>
                <x-ui.select
                    label="Role"
                    name="role"
                    id="edit-user-role"
                    :value="'admin'"
                    :options="['admin' => 'Admin', 'superadmin' => 'Super Admin']"
                    :placeholder-option="false"
                />
                <p id="edit-user-self-note" class="mt-1 hidden text-xs text-slate-400 dark:text-slate-500">Role akun Anda sendiri tidak dapat diubah.</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="ghost" type="button" data-modal-close>Batal</x-ui.button>
                <x-ui.button variant="primary" type="submit">Simpan Perubahan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
    <script>
        function fillEditModal(button) {
            const form = document.getElementById('edit-user-form');
            const isSelf = button.dataset.self === '1';
            const roleSelect = document.getElementById('edit-user-role');

            form.action = button.dataset.url;
            document.getElementById('edit-user-name').value = button.dataset.name;
            document.getElementById('edit-user-email').value = button.dataset.email;
            document.getElementById('edit-user-password').value = '';
            roleSelect.value = button.dataset.role;
            roleSelect.disabled = isSelf;
            document.getElementById('edit-user-self-note').classList.toggle('hidden', !isSelf);

            openModal('edit-user-modal');
        }
    </script>
</x-admin.layouts.app>
