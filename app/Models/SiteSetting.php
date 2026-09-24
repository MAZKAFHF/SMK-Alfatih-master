<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "site_setting:{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            if (! $row) {
                return $default;
            }

            return static::castValue($row->value, $row->type);
        });
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): static
    {
        $storeValue = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;
        $setting = static::updateOrCreate(['key' => $key], [
            'value' => $storeValue,
            'type' => $type,
            'group' => $group,
        ]);
        Cache::forget("site_setting:{$key}");
        Cache::forget('site_settings:all');

        return $setting;
    }

    public static function allCached(): Collection
    {
        return Cache::remember('site_settings:all', 3600, function () {
            return static::all()->mapWithKeys(fn ($row) => [$row->key => static::castValue($row->value, $row->type)]);
        });
    }

    private static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    public static function flushCache(): void
    {
        Cache::forget('site_settings:all');
        foreach (static::pluck('key') as $key) {
            Cache::forget("site_setting:{$key}");
        }
    }
}
