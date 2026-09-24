<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        $checks = [];
        $ok = true;

        try {
            DB::connection()->getPdo();
            DB::table('users')->limit(1)->count();
            $checks['database'] = 'ok';
        } catch (\Throwable $e) {
            $checks['database'] = 'fail: '.$e->getMessage();
            $ok = false;
        }

        try {
            Cache::put('health_check', 'ok', 10);
            $checks['cache'] = Cache::get('health_check') === 'ok' ? 'ok' : 'fail';
        } catch (\Throwable $e) {
            $checks['cache'] = 'fail';
            $ok = false;
        }

        $checks['storage'] = is_writable(storage_path()) ? 'ok' : 'fail';
        if ($checks['storage'] !== 'ok') {
            $ok = false;
        }

        return response()->json([
            'status' => $ok ? 'ok' : 'fail',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $ok ? 200 : 503);
    }
}
