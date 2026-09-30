<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AdminCodeService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function store(StoreUserRequest $request)
    {
        try {
            User::create([
                'name' => $request->string('name'),
                'email' => $request->string('email'),
                'password' => $request->string('password'),
                'is_admin' => true,
                'is_superadmin' => $request->input('role') === 'superadmin',
            ]);

            return back()->with('success', 'Akun admin baru berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan akun admin. Silakan coba lagi.');
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $data = [
                'name' => $request->string('name'),
                'email' => $request->string('email'),
            ];

            if ($request->filled('password')) {
                $data['password'] = $request->string('password');
            }

            $role = $request->input('role');

            if ($role !== null && ! $user->is(auth()->user())) {
                $data['is_superadmin'] = $role === 'superadmin';
            }

            $old = $user->toArray();
            $user->update($data);
            AuditService::log('user_update', $user, $old, $user->toArray());

            return back()->with('success', "Data akun {$user->name} berhasil diperbarui.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui data akun. Silakan coba lagi.');
        }
    }

    public function toggleActive(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }
        // Prevent last superadmin deactivation
        if ($user->is_superadmin && $user->is_active) {
            $otherActiveSuper = User::where('is_superadmin', true)->where('is_active', true)->where('id', '!=', $user->id)->count();
            if ($otherActiveSuper === 0) {
                return back()->with('error', 'Tidak dapat menonaktifkan superadmin terakhir yang aktif.');
            }
        }

        $old = $user->is_active;
        $user->update(['is_active' => ! $old]);
        AuditService::log($user->is_active ? 'user_activate' : 'user_deactivate', $user, ['is_active' => $old], ['is_active' => $user->is_active]);

        return back()->with('success', $user->is_active ? "Akun {$user->name} diaktifkan." : "Akun {$user->name} dinonaktifkan.");
    }

    public function destroy(Request $request, User $user)
    {
        AdminCodeService::verify($request);

        if ($user->is(auth()->user())) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        if ($user->is_superadmin) {
            $otherSuper = User::where('is_superadmin', true)->where('id', '!=', $user->id)->count();
            if ($otherSuper === 0) {
                return back()->with('error', 'Tidak dapat menghapus superadmin terakhir.');
            }
        }
        $label = $user->name;
        $user->delete();
        AuditService::log('user_delete', null, null, ['name' => $label, 'email' => $user->email]);

        return back()->with('success', "Akun {$label} dihapus.");
    }
}
