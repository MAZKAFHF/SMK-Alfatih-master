<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\PPDBRegistration;

class DashboardController extends Controller
{
    public function index()
    {
        $statuses = RegistrationStatus::cases();

        $stats = [];
        foreach ($statuses as $status) {
            $stats[$status->value] = PPDBRegistration::where('status', $status)->count();
        }
        $stats['total'] = array_sum($stats);
        $stats['today'] = PPDBRegistration::whereDate('created_at', today())->count();

        $recent = PPDBRegistration::query()
            ->with('program')
            ->latest()
            ->limit(6)
            ->get();

        $last7Days = collect(range(6, 0, -1))->map(function (int $daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'date' => $date->toDateString(),
                'label' => $date->translatedFormat('D'),
                'full' => $date->translatedFormat('j M'),
                'count' => PPDBRegistration::whereDate('created_at', $date)->count(),
            ];
        });

        $programDistribution = PPDBRegistration::query()
            ->join('programs', 'programs.id', '=', 'ppdb_registrations.program_id')
            ->selectRaw('programs.name, count(*) as total')
            ->groupBy('programs.id', 'programs.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'total' => (int) $item->total,
            ]);

        $recentLoginLogs = auth()->user()->is_superadmin
            ? LoginLog::query()->with('user')->latest('created_at')->limit(5)->get()
            : collect();

        return view('admin.dashboard', [
            'statuses' => $statuses,
            'stats' => $stats,
            'recent' => $recent,
            'last7Days' => $last7Days,
            'programDistribution' => $programDistribution,
            'recentLoginLogs' => $recentLoginLogs,
            'maxTrend' => max($last7Days->max('count'), 1),
        ]);
    }
}
