<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('programs.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('news.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('gallery.index'), 'priority' => '0.6', 'changefreq' => 'weekly'],
            ['loc' => route('announcements.index'), 'priority' => '0.6', 'changefreq' => 'weekly'],
            ['loc' => route('contact.index'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('ppdb.index'), 'priority' => '0.9', 'changefreq' => 'weekly'],
        ]);

        Program::active()->get()->each(fn ($program) => $urls->push([
            'loc' => route('programs.show', $program),
            'priority' => '0.7',
            'changefreq' => 'monthly',
            'lastmod' => $program->updated_at?->toAtomString(),
            'images' => $program->image ? [['loc' => $this->absoluteUrl($program->image), 'title' => $program->name]] : [],
        ]));

        News::published()->latest('published_at')->get()->each(function ($news) use ($urls): void {
            $lastModified = $news->updated_at && $news->published_at
                ? ($news->updated_at->gt($news->published_at) ? $news->updated_at : $news->published_at)
                : ($news->updated_at ?? $news->published_at);

            $urls->push([
                'loc' => route('news.show', $news),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $lastModified?->toAtomString(),
                'images' => $news->thumbnail ? [['loc' => $this->absoluteUrl($news->thumbnail), 'title' => $news->title]] : [],
            ]);
        });

        Page::published()->get()->each(fn ($page) => $urls->push([
            'loc' => route('pages.show', $page->slug),
            'priority' => '0.6',
            'changefreq' => 'monthly',
            'lastmod' => $page->updated_at?->toAtomString(),
            'images' => $page->image ? [['loc' => $this->absoluteUrl($page->image), 'title' => $page->title]] : [],
        ]));

        $xml = view('public.sitemap', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function absoluteUrl(string $url): string
    {
        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            ? $url
            : url($url);
    }
}
