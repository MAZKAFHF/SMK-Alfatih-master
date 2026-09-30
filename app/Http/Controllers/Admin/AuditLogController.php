<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AdminCodeService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($qq) use ($request) {
                $s = $request->string('search');
                $qq->where('auditable_label', 'like', "%{$s}%")->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"));
            }))
            ->latest('created_at')->paginate(25)->withQueryString();

        $actions = AuditLog::distinct()->pluck('action')->filter()->values();

        return view('admin.audit-logs.index', compact('logs', 'actions'));
    }

    public function clear(Request $request)
    {
        AdminCodeService::verify($request);
        $count = AuditLog::count();
        AuditLog::query()->delete();
        AuditService::log('audit_logs_cleared', null, null, ['deleted_count' => $count]);

        return redirect()->route('admin.audit-logs.index')->with('success', "{$count} audit log dibersihkan. Catatan pembersihan ini dipertahankan untuk keamanan.");
    }
}
