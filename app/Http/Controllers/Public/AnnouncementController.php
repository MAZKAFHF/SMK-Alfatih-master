<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::published()
            ->latest('published_at')
            ->paginate(10);

        return view('public.announcements.index', compact('announcements'))
            ->with('title', 'Pengumuman');
    }
}
