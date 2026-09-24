<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::published()
            ->with('author')
            ->latest('published_at')
            ->paginate(9);

        return view('public.news.index', compact('news'))
            ->with('title', 'Berita');
    }

    public function show(News $news)
    {
        abort_if($news->status !== ContentStatus::Published, 404);
        abort_if($news->published_at?->gt(now()), 404);

        $related = News::published()
            ->whereKeyNot($news->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        $news->load('author');

        return view('public.news.show', compact('news', 'related'))
            ->with('title', $news->title)
            ->with('description', $news->excerpt);
    }
}
