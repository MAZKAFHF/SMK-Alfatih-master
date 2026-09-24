<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\AuditService;
use App\Services\HtmlSanitizer;
use App\Services\PublicCacheService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::query()
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', "%{$request->string('search')}%"))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('published_at')->latest()->paginate(15)->withQueryString();
        $trashedCount = Announcement::onlyTrashed()->count();

        return view('admin.announcements.index', compact('announcements', 'trashedCount'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content'], 'strict');
        if (empty($data['published_at']) && $data['status'] === 'published') {
            $data['published_at'] = now();
        } elseif (! empty($data['published_at'])) {
            // datetime-local from browser is Jakarta local time - convert to UTC for consistent storage
            $data['published_at'] = \Carbon\Carbon::parse($data['published_at'], 'Asia/Jakarta')->utc();
        }
        $ann = Announcement::create($data);
        AuditService::log('announcement_create', $ann, null, $data);
        $this->clearPublicCache();

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $old = $announcement->toArray();
        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content'], 'strict');
        if (! empty($data['published_at'])) {
            $data['published_at'] = \Carbon\Carbon::parse($data['published_at'], 'Asia/Jakarta')->utc();
        }
        $announcement->update($data);
        AuditService::log('announcement_update', $announcement, $old, $announcement->toArray());
        $this->clearPublicCache();

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        AuditService::log('announcement_delete', $announcement);
        $this->clearPublicCache();

        return back()->with('success', 'Pengumuman dipindahkan ke Trash.');
    }

    public function trash()
    {
        $announcements = Announcement::onlyTrashed()->latest()->paginate(15);

        return view('admin.announcements.trash', compact('announcements'));
    }

    public function restore(int $id)
    {
        $a = Announcement::onlyTrashed()->findOrFail($id);
        $a->restore();
        AuditService::log('announcement_restore', $a);
        $this->clearPublicCache();

        return back()->with('success', 'Pengumuman dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        $a = Announcement::onlyTrashed()->findOrFail($id);
        $label = $a->title;
        $a->forceDelete();
        AuditService::log('announcement_force_delete', null, null, ['title' => $label]);
        $this->clearPublicCache();

        return back()->with('success','Pengumuman dihapus permanen.');
    }

    private function clearPublicCache(): void
    {
        PublicCacheService::forgetAnnouncements();
    }
}
