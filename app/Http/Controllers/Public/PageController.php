<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $view = $slug === 'profil' ? 'public.pages.profil' : 'public.pages.show';

        $data = ['page' => $page];

        if ($slug === 'profil') {
            $data['visiMisi'] = Page::published()->where('slug', 'visi-misi')->first();
            $data['sambutan'] = Page::published()->where('slug', 'sambutan-kepala-sekolah')->first();
            $data['fasilitas'] = Page::published()->where('slug', 'fasilitas')->first();
            $data['sejarah'] = Page::published()->where('slug', 'sejarah')->first();
            $data['programs'] = \App\Models\Program::active()->orderBy('order')->get();
            $data['foundedYear'] = \App\Models\SiteSetting::get('founding_year');
        }

        return view($view, $data)
            ->with('title', $page->title)
            ->with('description', $page->meta_description ?? strip_tags($page->content));
    }
}
