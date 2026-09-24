<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PublicCacheService
{
    public const HOME_PROGRAMS = 'home:programs';
    public const HOME_NEWS = 'home:news';
    public const HOME_ANNOUNCEMENTS = 'home:announcements';
    public const HOME_GALLERIES = 'home:galleries';
    public const HOME_STATS = 'site:stats';
    public const NAV_PAGES = 'nav_pages';
    public const SITE_SETTINGS_ALL = 'site_settings:all';
    public const PPDB_CURRENT = 'ppdb_setting:current';

    public static function forgetPrograms(): void
    {
        Cache::forget(self::HOME_PROGRAMS);
        // programs also affect nav? No, but clear nav for safety if program affects PPDB choices
        Cache::forget(self::HOME_GALLERIES);
    }

    public static function forgetNews(): void
    {
        Cache::forget(self::HOME_NEWS);
        Cache::forget(self::HOME_ANNOUNCEMENTS); // news and announcements share homepage cache group
    }

    public static function forgetAnnouncements(): void
    {
        Cache::forget(self::HOME_ANNOUNCEMENTS);
        Cache::forget(self::HOME_NEWS);
    }

    public static function forgetGalleries(): void
    {
        Cache::forget(self::HOME_GALLERIES);
    }

    public static function forgetPages(): void
    {
        Cache::forget(self::NAV_PAGES);
        // sitemap also depends on pages, but sitemap is not cached currently
    }

    public static function forgetSettings(): void
    {
        Cache::forget(self::SITE_SETTINGS_ALL);
        // also per-key site_setting:{key} will be forgotten via SiteSetting::flushCache, but also clear nav/home that may use settings
        Cache::forget(self::NAV_PAGES);
        Cache::forget(self::HOME_PROGRAMS);
        Cache::forget(self::HOME_NEWS);
        Cache::forget(self::HOME_ANNOUNCEMENTS);
        Cache::forget(self::HOME_GALLERIES);
        Cache::forget(self::HOME_STATS);
    }

    public static function forgetPpdbSettings(): void
    {
        Cache::forget(self::PPDB_CURRENT);
    }

    public static function forgetAllPublicContent(): void
    {
        Cache::forget(self::HOME_PROGRAMS);
        Cache::forget(self::HOME_NEWS);
        Cache::forget(self::HOME_ANNOUNCEMENTS);
        Cache::forget(self::HOME_GALLERIES);
        Cache::forget(self::HOME_STATS);
        Cache::forget(self::NAV_PAGES);
    }

    public static function flushIfNeeded(string $entity): void
    {
        match ($entity) {
            'program' => self::forgetPrograms(),
            'news' => self::forgetNews(),
            'announcement' => self::forgetAnnouncements(),
            'gallery' => self::forgetGalleries(),
            'page' => self::forgetPages(),
            'setting' => self::forgetSettings(),
            'ppdb' => self::forgetPpdbSettings(),
            default => null,
        };
    }
}
