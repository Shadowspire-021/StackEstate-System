<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CacheService;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group'];

    /**
     * Cache key for all settings.
     */
    protected const CACHE_KEY_ALL = 'all';

    /**
     * Cache key for grouped settings.
     */
    protected const CACHE_KEY_GROUPED = 'grouped';

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = self::getAllAsArrayCached();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value by key
     */
    public static function setValue(string $key, mixed $value, string $group = 'general'): static
    {
        $result = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        self::invalidateCache();

        return $result;
    }

    /**
     * Get all settings grouped by group (cached)
     *
     * @return array<string, array<string, string>>
     */
    public static function getGrouped(): array
    {
        return self::getGroupedCached();
    }

    /**
     * Get all settings in the legacy format (key => value) - Cached
     *
     * @return array<string, string>
     */
    public static function getAllAsArray(): array
    {
        return self::getAllAsArrayCached();
    }

    /**
     * Get all settings as array with caching.
     *
     * @return array<string, string>
     */
    public static function getAllAsArrayCached(): array
    {
        return CacheService::remember(
            CacheService::key(CacheService::PREFIX_SETTINGS, self::CACHE_KEY_ALL),
            CacheService::TTL_LONG,
            fn () => static::pluck('value', 'key')->toArray()
        );
    }

    /**
     * Get grouped settings with caching.
     *
     * @return array<string, array<string, string>>
     */
    public static function getGroupedCached(): array
    {
        return CacheService::remember(
            CacheService::key(CacheService::PREFIX_SETTINGS, self::CACHE_KEY_GROUPED),
            CacheService::TTL_LONG,
            fn () => static::all()->groupBy('group')->map(fn ($items) => $items->pluck('value', 'key')->toArray())->toArray()
        );
    }

    /**
     * Save multiple settings for a group
     */
    public static function saveGroup(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }

        self::invalidateCache();
    }

    /**
     * Invalidate all settings cache.
     */
    public static function invalidateCache(): void
    {
        CacheService::forget(CacheService::key(CacheService::PREFIX_SETTINGS, self::CACHE_KEY_ALL));
        CacheService::forget(CacheService::key(CacheService::PREFIX_SETTINGS, self::CACHE_KEY_GROUPED));
    }
}
