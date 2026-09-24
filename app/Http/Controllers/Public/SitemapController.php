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

        Program::active()->get()->each(fn ($p) => $urls->push(['loc' => route('programs.show', $p), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $p->updated_at?->toAtomString()]));
        News::published()->latest('published_at')->get()->each(fn ($n) => $urls->push(['loc' => route('news.show', $n), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $n->published_at?->toAtomString() ?? $n->updated_at->toAtomString()]));
        Page::published()->get()->each(fn ($pg) => $urls->push(['loc' => route('pages.show', $pg->slug), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $pg->updated_at->toAtomString()]));

        $xml = view('public.sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
