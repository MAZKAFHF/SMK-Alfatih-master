<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $availability = \App\Services\PpdbAvailability::resolveForApplicationCreation();
        $period = $availability->period;
        $all = $user->applications()->with(['program', 'period', 'documents', 'appointment.slot', 'decision'])->latest()->get();
        // Kelompok periode: SAAT INI (periode berjalan) vs RIWAYAT (periode lama).
        $hasCurrentCycle = in_array($availability->status, [
            \App\Services\PpdbAvailability::OPEN,
            \App\Services\PpdbAvailability::UPCOMING,
            \App\Services\PpdbAvailability::FULL,
        ], true);
        $current = $hasCurrentCycle
            ? $all->filter(fn ($a) => $a->period_id && $period && (int) $a->period_id === (int) $period->id)
            : collect();
        $history = $hasCurrentCycle
            ? $all->reject(fn ($a) => $a->period_id && $period && (int) $a->period_id === (int) $period->id)
            : $all;
        $canRegister = $availability->canCreateApplication();
        $notifications = $user->notifications()->latest()->limit(8)->get();
        $unread = $user->notifications()->unread()->count();

        return view('portal.dashboard', compact('period', 'availability', 'current', 'history', 'canRegister', 'notifications', 'unread'));
    }

    public function notifications(Request $request)
    {
        $user = $request->user();
        $items = $user->notifications()->with('application')->latest()->paginate(15);

        return view('portal.notifications', compact('items'));
    }

    public function readNotification(Request $request, int $id)
    {
        $note = $request->user()->notifications()->findOrFail($id);
        $note->update(['read_at' => now()]);

        return redirect($note->action_url ?: route('portal.dashboard'));
    }
}
