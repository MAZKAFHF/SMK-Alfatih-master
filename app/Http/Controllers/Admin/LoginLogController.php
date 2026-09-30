<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Services\AdminCodeService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = LoginLog::query()
            ->with('user')
            ->when($request->filled('event'), function ($query) use ($request) {
                $query->where('event', $request->string('event'));
            })
            ->when($request->filled('channel'), function ($query) use ($request) {
                $query->where('channel', $request->string('channel'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('attempted_email', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('user_agent', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.login-logs.index', compact('logs'));
    }

    public function clear(Request $request)
    {
        AdminCodeService::verify($request);
        $count = LoginLog::count();
        LoginLog::query()->delete();
        AuditService::log('login_logs_cleared', null, null, ['deleted_count' => $count]);

        return redirect()->route('admin.login-logs.index')->with('success', "{$count} log login berhasil dibersihkan.");
    }
}
