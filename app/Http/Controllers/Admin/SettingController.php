<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Services\AuditService;
use App\Services\MediaService;
use App\Services\PublicCacheService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::all()->groupBy('group');

        $coreSlugs = ['profil', 'sejarah', 'visi-misi', 'sambutan-kepala-sekolah', 'fasilitas'];
        $pages = Page::whereIn('slug', $coreSlugs)->get()->keyBy('slug');
        // Sort in defined order (SQLite compatible, no FIELD)
        $pages = collect($coreSlugs)->mapWithKeys(fn ($slug) => [$slug => $pages[$slug] ?? null]);
        $customPages = Page::whereNotIn('slug', $coreSlugs)->orderBy('order')->orderBy('title')->get();

        return view('admin.settings.index', compact('settings', 'pages', 'customPages'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'school_name' => ['nullable', 'string', 'max:150'],
            'school_tagline' => ['nullable', 'string', 'max:500'],
            'school_email' => ['nullable', 'email', 'max:150'],
            'school_phone' => ['nullable', 'string', 'max:30'],
            'school_whatsapp' => ['nullable', 'string', 'max:30'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'headmaster_name' => ['nullable', 'string', 'max:150'],
            'founding_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'stat_programs' => ['nullable', 'string', 'max:30'],
            'stat_founded' => ['nullable', 'string', 'max:30'],
            'stat_students' => ['nullable', 'string', 'max:30'],
            'stat_alumni' => ['nullable', 'string', 'max:30'],
            'seo_title' => ['nullable', 'string', 'max:150'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,jpg', 'max:1024'],
            'social_instagram' => ['nullable', 'url', 'max:500', function ($attr, $value, $fail) {
                if (filled($value) && ! str_contains(strtolower((string) $value), 'instagram.com')) {
                    $fail('Link Instagram harus berupa URL Instagram yang valid (contoh: https://www.instagram.com/smktahfizhalfatihpku/).');
                }
            }],
            'social_youtube' => ['nullable', 'url', 'max:500', function ($attr, $value, $fail) {
                $v = strtolower((string) $value);
                if (filled($value) && ! str_contains($v, 'youtube.com') && ! str_contains($v, 'youtu.be')) {
                    $fail('Link YouTube harus berupa URL YouTube yang valid (contoh: https://www.youtube.com/@SMKTAHFIZHALFATIH).');
                }
            }],
            'social_facebook' => ['nullable', 'url', 'max:500', function ($attr, $value, $fail) {
                $v = strtolower((string) $value);
                if (filled($value) && ! str_contains($v, 'facebook.com') && ! str_contains($v, 'fb.com')) {
                    $fail('Link Facebook harus berupa URL Facebook yang valid.');
                }
            }],
            'social_tiktok' => ['nullable', 'url', 'max:500', function ($attr, $value, $fail) {
                if (filled($value) && ! str_contains(strtolower((string) $value), 'tiktok.com')) {
                    $fail('Link TikTok harus berupa URL TikTok yang valid.');
                }
            }],
        ], [
            'social_instagram.url' => 'Link Instagram harus berupa alamat web yang valid diawali http(s)://.',
            'social_youtube.url' => 'Link YouTube harus berupa alamat web yang valid diawali http(s)://.',
            'social_facebook.url' => 'Link Facebook harus berupa alamat web yang valid diawali http(s)://.',
            'social_tiktok.url' => 'Link TikTok harus berupa alamat web yang valid diawali http(s)://.',
        ]);

        $map = [
            'school_name' => 'general', 'school_tagline' => 'general', 'school_email' => 'contact', 'school_phone' => 'contact', 'school_whatsapp' => 'contact',
            'school_address' => 'contact', 'maps_url' => 'contact', 'headmaster_name' => 'general', 'founding_year' => 'general',
            'stat_programs' => 'homepage', 'stat_founded' => 'homepage', 'stat_students' => 'homepage', 'stat_alumni' => 'homepage',
            'seo_title' => 'seo', 'seo_description' => 'seo',
            'social_instagram' => 'social', 'social_youtube' => 'social', 'social_facebook' => 'social', 'social_tiktok' => 'social',
        ];

        foreach ($map as $key => $group) {
            // exists() (bukan has()) agar admin bisa mengosongkan URL
            // untuk menyembunyikan platform dari website publik.
            if ($request->exists($key)) {
                SiteSetting::set($key, trim((string) $request->input($key)), 'string', $group);
            }
        }

        if ($request->hasFile('logo')) {
            $old = SiteSetting::where('key', 'logo')->first()?->value;
            $path = MediaService::replace($request->file('logo'), 'settings', $old, 400);
            SiteSetting::set('logo', $path, 'string', 'general');
        }
        if ($request->hasFile('favicon')) {
            $old = SiteSetting::where('key', 'favicon')->first()?->value;
            $path = MediaService::replace($request->file('favicon'), 'settings', $old, 128);
            SiteSetting::set('favicon', $path, 'string', 'general');
        }

        SiteSetting::flushCache();
        PublicCacheService::forgetSettings();
        AuditService::log('settings_update', null, null, $request->except(['logo', 'favicon', '_token']));

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
