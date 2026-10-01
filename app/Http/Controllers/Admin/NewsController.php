<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Models\News;
use App\Services\AuditService;
use App\Services\HtmlSanitizer;
use App\Services\JakartaDateTime;
use App\Services\MediaService;
use App\Services\PublicCacheService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with('author')
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', "%{$request->string('search')}%")->orWhere('slug', 'like', "%{$request->string('search')}%"))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('published_at')->latest()
            ->paginate(15)->withQueryString();
        $trashedCount = News::onlyTrashed()->count();

        return view('admin.news.index', compact('news', 'trashedCount'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function store(StoreNewsRequest $request)
    {
        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content']);
        $data['author_id'] = auth()->id();
        if (empty($data['published_at']) && $data['status'] === 'published') {
            $data['published_at'] = now();
        } elseif (! empty($data['published_at'])) {
            $data['published_at'] = JakartaDateTime::toStorage($data['published_at'], 'published_at');
        }
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = MediaService::store($request->file('thumbnail'), 'news', 1200);
        }
        $item = News::create($data);
        AuditService::log('news_create', $item, null, $data);
        $this->clearCache();

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    public function update(UpdateNewsRequest $request, News $news)
    {
        $old = $news->toArray();
        $data = $request->validated();
        $data['content'] = HtmlSanitizer::clean($data['content']);
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = MediaService::replace(
                $request->file('thumbnail'),
                'news',
                $news->getRawOriginal('thumbnail'),
                1200,
            );
        } else {
            unset($data['thumbnail']);
        }
        if (! empty($data['published_at'])) {
            $data['published_at'] = JakartaDateTime::toStorage($data['published_at'], 'published_at');
        } elseif ($data['status'] === 'published' && empty($data['published_at']) && empty($news->published_at)) {
            $data['published_at'] = now();
        }
        $news->update($data);
        AuditService::log('news_update', $news, $old, $news->toArray());
        $this->clearCache();

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        AuditService::log('news_delete', $news);
        $this->clearCache();

        return back()->with('success', 'Berita dipindahkan ke Trash.');
    }

    public function trash(Request $request)
    {
        $news = News::onlyTrashed()->with('author')->latest()->paginate(15)->withQueryString();

        return view('admin.news.trash', compact('news'));
    }

    public function restore(int $id)
    {
        $item = News::onlyTrashed()->findOrFail($id);
        $item->restore();
        AuditService::log('news_restore', $item);
        $this->clearCache();

        return back()->with('success', 'Berita dipulihkan.');
    }

    public function forceDelete(int $id)
    {
        $item = News::onlyTrashed()->findOrFail($id);
        if ($item->getRawOriginal('thumbnail')) {
            MediaService::delete($item->getRawOriginal('thumbnail'));
        }
        $label = $item->title;
        $item->forceDelete();
        AuditService::log('news_force_delete', null, null, ['title' => $label]);
        $this->clearCache();

        return back()->with('success', 'Berita dihapus permanen.');
    }

    private function clearCache(): void
    {
        PublicCacheService::forgetNews();
    }
}
