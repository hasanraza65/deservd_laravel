<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    private const CACHE_KEY = 'settings.all';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** All settings, cast to their declared type, keyed by setting key. Cached — read on every request. */
    public static function allCast(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::all()->mapWithKeys(fn (Setting $setting) => [
                $setting->key => self::cast($setting->value, $setting->type),
            ])->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::allCast()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        $stored = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        static::updateOrCreate(['key' => $key], ['value' => $stored, 'type' => $type]);
    }

    private static function cast(?string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => (bool) $value,
            'json' => json_decode((string) $value, true),
            default => $value,
        };
    }
}
