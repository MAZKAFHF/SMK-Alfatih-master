<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Gallery;
use App\Models\News;
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

        $stats = Cache::remember('site:stats', 3600, function () {
            return [
                'programs' => SiteSetting::get('stat_programs', '4'),
                'founded' => SiteSetting::get('stat_founded', '2016'),
                'students' => SiteSetting::get('stat_students', '850+'),
                'alumni' => SiteSetting::get('stat_alumni', '1200+'),
            ];
        });

        return view('public.home', compact('programs', 'news', 'announcements', 'galleries', 'stats'));
    }
}
