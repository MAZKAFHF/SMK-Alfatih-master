<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return view('public.pages.show', [
            'page' => $page,
        ])->with('title', $page->title)
            ->with('description', $page->meta_description ?? strip_tags($page->content));
    }
}
