<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use App\Services\AuditService;
use App\Services\HtmlSanitizer;
use App\Services\MediaService;
use App\Services\PublicCacheService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $pages = Page::query()
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', "%{$request->string('search')}%"))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('order')->latest()->paginate(20)->withQueryString();
        $trashedCount = Page::onlyTrashed()->count();

        return view('admin.pages.index', compact('pages', 'trashedCount'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(StorePageRequest $request)
    {
        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content']);
        if ($request->hasFile('image')) {
            $data['image'] = MediaService::store($request->file('image'), 'pages', 1400);
        }
        $page = Page::create($data);
        PublicCacheService::forgetPages();
        AuditService::log('page_create', $page, null, $data);

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil ditambahkan.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(UpdatePageRequest $request, Page $page)
    {
        $old = $page->toArray();
        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content']);
        if ($request->hasFile('image')) {
            $data['image'] = MediaService::replace(
                $request->file('image'),
                'pages',
                $page->getRawOriginal('image'),
                1400,
            );
        } else {
            unset($data['image']);
        }
        $page->update($data);
        PublicCacheService::forgetPages();
        AuditService::log('page_update', $page, $old, $page->toArray());

        return redirect()->route('admin.pages.index')->with('success', 'Halaman diperbarui.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        PublicCacheService::forgetPages();
        AuditService::log('page_delete', $page);

        return back()->with('success', 'Halaman dipindahkan ke Trash.');
    }

    public function trash()
    {
        $pages = Page::onlyTrashed()->latest()->paginate(20);

        return view('admin.pages.trash', compact('pages'));
    }

    public function restore(int $id)
    {
        $p = Page::onlyTrashed()->findOrFail($id);
        $p->restore();
        PublicCacheService::forgetPages();
        AuditService::log('page_restore', $p);

        return back()->with('success', 'Halaman dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        $p = Page::onlyTrashed()->findOrFail($id);
        if ($p->getRawOriginal('image')) {
            MediaService::delete($p->getRawOriginal('image'));
        }
        $label = $p->title;
        $p->forceDelete();
        PublicCacheService::forgetPages();
        AuditService::log('page_force_delete', null, null, ['title' => $label]);

        return back()->with('success', 'Halaman dihapus permanen.');
    }
}
