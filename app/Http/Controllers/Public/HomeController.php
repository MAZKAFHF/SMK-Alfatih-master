<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $programs = Cache::remember('home:programs', 300, fn () => Program::active()->orderBy('order')->limit(4)->get());
        $news = Cache::remember('home:news', 300, fn () => News::published()->latest('published_at')->limit(3)->get());
        $announcements = Cache::remember('home:announcements', 300, fn () => Announcement::published()->latest('published_at')->limit(4)->get());
        $galleries = Cache::remember('home:galleries', 300, fn () => Gallery::published()->orderBy('order')->limit(6)->get());
        $sambutan = Cache::remember('home:sambutan', 300, fn () => Page::published()->where('slug', 'sambutan-kepala-sekolah')->first());
        $headmasterName = SiteSetting::get('headmaster_name');
        $schoolTagline = SiteSetting::get('school_tagline');

        $stats = Cache::remember('site:stats:v2', 3600, function () {
            return [
                // Program count is derived from live data. The other values are
                // intentionally blank when the school has not verified them in
                // Settings; public pages must never invent institutional facts.
                'programs' => (string) Program::active()->count(),
                'founded' => SiteSetting::get('stat_founded'),
                'students' => SiteSetting::get('stat_students'),
                'alumni' => SiteSetting::get('stat_alumni'),
            ];
        });

        // Status PPDB kanonis — SATU kebenaran dengan /ppdb, portal, dashboard.
        $ppdbState = \App\Services\PpdbAvailability::resolvePublic();

        return view('public.home', compact('programs', 'news', 'announcements', 'galleries', 'sambutan', 'headmasterName', 'schoolTagline', 'stats', 'ppdbState'));
    }
}
