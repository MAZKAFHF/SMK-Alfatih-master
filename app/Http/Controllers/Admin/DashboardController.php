<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\InterviewAppointment;
use App\Models\LoginLog;
use App\Models\PPDBRegistration;
use App\Services\PpdbContext;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $ctx = PpdbContext::resolveFromRequest($request);
        $period = $ctx['period'];
        $mode = $ctx['mode'];
        $periodId = $period?->id;

        $statuses = ApplicationStatus::cases();

        // SEMUA metrik PPDB di-scope periode. Tanpa periode => nol, bukan global.
        $stats = [];
        foreach ($statuses as $status) {
            $stats[$status->value] = $periodId
                ? PPDBRegistration::where('period_id', $periodId)->where('application_status', $status)->count()
                : 0;
        }
        $stats['total'] = array_sum($stats);
        $stats['needs_verification'] = ($stats['submitted'] ?? 0) + ($stats['resubmitted'] ?? 0);
        $stats['today'] = $periodId
            ? PPDBRegistration::where('period_id', $periodId)->whereDate('created_at', today())->count()
            : 0;

        $recent = $periodId
            ? PPDBRegistration::query()->with('program')->where('period_id', $periodId)->latest()->limit(6)->get()
            : collect();

        // Tren 7 hari: hanya periode konteks, pengelompokan tanggal Asia/Jakarta.
        $last7Days = collect(range(6, 0, -1))->map(function (int $daysAgo) use ($periodId) {
            $date = now('Asia/Jakarta')->subDays($daysAgo);
            $count = $periodId
                ? PPDBRegistration::where('period_id', $periodId)->whereDate('created_at', $date->toDateString())->count()
                : 0;

            return [
                'date' => $date->toDateString(),
                'label' => $date->translatedFormat('D'),
                'full' => $date->translatedFormat('j M'),
                'count' => $count,
            ];
        });

        $programDistribution = $periodId
            ? PPDBRegistration::query()
                ->join('programs', 'programs.id', '=', 'ppdb_registrations.program_id')
                ->where('ppdb_registrations.period_id', $periodId)
                ->selectRaw('programs.name, count(*) as total')
                ->groupBy('programs.id', 'programs.name')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($item) => ['name' => $item->name, 'total' => (int) $item->total])
            : collect();

        // Wawancara hari ini: hanya appointment yang slot-nya milik periode konteks.
        $todayInterviews = $periodId
            ? InterviewAppointment::with(['application', 'slot'])
                ->whereHas('slot', fn ($q) => $q->where('period_id', $periodId)->whereDate('date', today('Asia/Jakarta')))
                ->orderBy('id')
                ->get()
            : collect();

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
            'period' => $period,
            'mode' => $mode,
            'modeLabel' => PpdbContext::modeLabel($mode),
            'periodOptions' => PpdbContext::options(),
            'todayInterviews' => $todayInterviews,
        ]);
    }
}
