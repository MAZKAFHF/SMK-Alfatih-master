<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbSetting;
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
        $ppdb = PpdbSetting::current();

        $coreSlugs = ['profil','sejarah','visi-misi','sambutan-kepala-sekolah','fasilitas'];
        $pages = \App\Models\Page::whereIn('slug', $coreSlugs)->get()->keyBy('slug');
        // Sort in defined order (SQLite compatible, no FIELD)
        $pages = collect($coreSlugs)->mapWithKeys(fn($slug) => [$slug => $pages[$slug] ?? null])->filter();
        // Ensure missing core pages are created as draft placeholders (in case seeder not run)
        foreach ($coreSlugs as $slug) {
            if (! isset($pages[$slug])) {
                $pages[$slug] = \App\Models\Page::create([
                    'title' => ucwords(str_replace('-',' ', $slug)),
                    'slug' => $slug,
                    'content' => '<p>Tulis konten '.ucwords(str_replace('-',' ', $slug)).' di sini.</p>',
                    'status' => 'draft',
                    'order' => array_search($slug, $coreSlugs) + 1,
                ]);
            }
        }
        $customPages = \App\Models\Page::whereNotIn('slug', $coreSlugs)->orderBy('order')->orderBy('title')->get();

        return view('admin.settings.index', compact('settings', 'ppdb', 'pages', 'customPages'));
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
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_youtube' => ['nullable', 'url', 'max:500'],
            'social_facebook' => ['nullable', 'url', 'max:500'],
            'social_tiktok' => ['nullable', 'url', 'max:500'],
        ]);

        $map = [
            'school_name' => 'general', 'school_tagline' => 'general', 'school_email' => 'contact', 'school_phone' => 'contact', 'school_whatsapp' => 'contact',
            'school_address' => 'contact', 'maps_url' => 'contact', 'headmaster_name' => 'general', 'founding_year' => 'general',
            'stat_programs' => 'homepage', 'stat_founded' => 'homepage', 'stat_students' => 'homepage', 'stat_alumni' => 'homepage',
            'seo_title' => 'seo', 'seo_description' => 'seo',
            'social_instagram' => 'social', 'social_youtube' => 'social', 'social_facebook' => 'social', 'social_tiktok' => 'social',
        ];

        foreach ($map as $key => $group) {
            if ($request->has($key)) {
                SiteSetting::set($key, $request->input($key), 'string', $group);
            }
        }

        if ($request->hasFile('logo')) {
            $old = SiteSetting::where('key', 'logo')->first()?->value;
            if ($old) {
                MediaService::delete($old);
            }
            $path = MediaService::store($request->file('logo'), 'settings', 400);
            SiteSetting::set('logo', $path, 'string', 'general');
        }
        if ($request->hasFile('favicon')) {
            $old = SiteSetting::where('key', 'favicon')->first()?->value;
            if ($old) {
                MediaService::delete($old);
            }
            $path = MediaService::store($request->file('favicon'), 'settings', 128);
            SiteSetting::set('favicon', $path, 'string', 'general');
        }

        SiteSetting::flushCache();
        PublicCacheService::forgetSettings();
        AuditService::log('settings_update', null, null, $request->except(['logo', 'favicon', '_token']));

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function ppdbUpdate(Request $request)
    {
        $request->validate([
            'academic_year' => ['required', 'string', 'max:20'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after:opens_at'],
            'is_open' => ['nullable', 'boolean'],
            'status_override' => ['nullable', 'in:open,closed,'],
            'announcement' => ['nullable', 'string', 'max:2000'],
            'quota' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'contact_info' => ['nullable', 'string', 'max:500'],
        ]);

        $ppdb = PpdbSetting::current();
        $old = $ppdb->toArray();
        $opensAt = $request->input('opens_at') ? \Carbon\Carbon::parse($request->input('opens_at'), 'Asia/Jakarta')->utc() : null;
        $closesAt = $request->input('closes_at') ? \Carbon\Carbon::parse($request->input('closes_at'), 'Asia/Jakarta')->utc() : null;
        $ppdb->update([
            'academic_year' => $request->input('academic_year'),
            'opens_at' => $opensAt,
            'closes_at' => $closesAt,
            'is_open' => $request->boolean('is_open'),
            'status_override' => $request->input('status_override') ?: null,
            'announcement' => $request->input('announcement'),
            'quota' => $request->input('quota'),
            'contact_info' => $request->input('contact_info'),
        ]);
        PpdbSetting::flushCache();
        AuditService::log('ppdb_settings_update', $ppdb, $old, $ppdb->toArray());

        return back()->with('success','Pengaturan PPDB diperbarui.');
    }
}
