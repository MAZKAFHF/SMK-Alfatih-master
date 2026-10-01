<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryRequest;
use App\Models\Gallery;
use App\Services\AuditService;
use App\Services\MediaService;
use App\Services\PublicCacheService;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $galleries = Gallery::query()
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', "%{$request->string('search')}%"))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('order')->latest()->paginate(20)->withQueryString();
        $categories = Gallery::distinct()->pluck('category')->filter()->values();
        $trashedCount = Gallery::onlyTrashed()->count();

        return view('admin.galleries.index', compact('galleries', 'categories', 'trashedCount'));
    }

    public function create()
    {
        $categories = Gallery::distinct()->pluck('category')->filter()->values();

        return view('admin.galleries.create', compact('categories'));
    }

    public function store(StoreGalleryRequest $request)
    {
        $data = $request->validated();
        $data['image'] = MediaService::store($request->file('image'), 'galleries', 1600);
        $gallery = Gallery::create($data);
        AuditService::log('gallery_create', $gallery, null, $data);
        PublicCacheService::forgetGalleries();

        return redirect()->route('admin.galleries.index')->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    public function edit(Gallery $gallery)
    {
        $categories = Gallery::distinct()->pluck('category')->filter()->values();

        return view('admin.galleries.edit', compact('gallery', 'categories'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'status' => ['required', 'in:draft,published,archived'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);
        $old = $gallery->toArray();
        $data = $request->only(['title', 'category', 'status', 'order']);
        if ($request->hasFile('image')) {
            $data['image'] = MediaService::replace(
                $request->file('image'),
                'galleries',
                $gallery->getRawOriginal('image'),
                1600,
            );
        }
        $gallery->update($data);
        AuditService::log('gallery_update', $gallery, $old, $gallery->toArray());
        PublicCacheService::forgetGalleries();

        return redirect()->route('admin.galleries.index')->with('success', 'Galeri berhasil diperbarui.');
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->delete();
        AuditService::log('gallery_delete', $gallery);
        PublicCacheService::forgetGalleries();

        return back()->with('success', 'Galeri dipindahkan ke Trash.');
    }

    public function trash(Request $request)
    {
        $galleries = Gallery::onlyTrashed()->latest()->paginate(20)->withQueryString();

        return view('admin.galleries.trash', compact('galleries'));
    }

    public function restore(int $id)
    {
        $g = Gallery::onlyTrashed()->findOrFail($id);
        $g->restore();
        AuditService::log('gallery_restore', $g);
        PublicCacheService::forgetGalleries();

        return back()->with('success', 'Galeri dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        $g = Gallery::onlyTrashed()->findOrFail($id);
        if ($g->getRawOriginal('image')) {
            MediaService::delete($g->getRawOriginal('image'));
        }
        $label = $g->title;
        $g->forceDelete();
        AuditService::log('gallery_force_delete', null, null, ['title' => $label]);
        PublicCacheService::forgetGalleries();

        return back()->with('success', 'Galeri dihapus permanen.');
    }
}
