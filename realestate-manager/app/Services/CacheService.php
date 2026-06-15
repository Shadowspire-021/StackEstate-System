<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    public const TTL_SHORT = 60;        // 1 minute
    public const TTL_MEDIUM = 300;      // 5 minutes
    public const TTL_LONG = 3600;       // 1 hour
    public const TTL_DAY = 86400;       // 24 hours

    /**
     * Cache key prefixes
     */
    public const PREFIX_SETTINGS = 'settings';
    public const PREFIX_DASHBOARD = 'dashboard';
    public const PREFIX_SEARCH = 'search';
    public const PREFIX_UNIT_STATS = 'unit_stats';
    public const PREFIX_CACHE_GEN = 'cache_gen';

    /**
     * Get value from cache or compute and store.
     *
     * @param  string  $key
     * @param  int  $ttl
     * @param  callable  $callback
     * @return mixed
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        if (!self::isEnabled()) {
            return $callback();
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get value from cache or compute and store (with tag support for database cache).
     *
     * @param  string  $key
     * @param  int  $ttl
     * @param  callable  $callback
     * @return mixed
     */
    public static function rememberWithTags(array $tags, string $key, int $ttl, callable $callback): mixed
    {
        if (!self::isEnabled()) {
            return $callback();
        }

        if (config('cache.default') === 'database' && method_exists(Cache::store(), 'tags')) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Forget a single cache key.
     *
     * @param  string  $key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        if (!self::isEnabled()) {
            return true;
        }

        return Cache::forget($key);
    }

    /**
     * Flush entire cache.
     *
     * @return bool
     */
    public static function flush(): bool
    {
        if (!self::isEnabled()) {
            return true;
        }

        return Cache::flush();
    }

    /**
     * Invalidate all cache entries sharing a prefix by bumping a generation counter.
     * Works with any cache driver (file, database, redis) — no tag dependency.
     *
     * Old cache files become orphaned and are naturally cleaned by TTL expiration.
     *
     * @param  string  $prefix  One of the PREFIX_* constants
     */
    public static function invalidateByPrefix(string $prefix): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $genKey = self::key(self::PREFIX_CACHE_GEN, $prefix);
        $version = (int) Cache::get($genKey, 0);
        Cache::forever($genKey, $version + 1);
    }

    /**
     * Get the current generation version for a cache prefix.
     * Used as part of the cache key so old entries are automatically orphaned.
     *
     * @param  string  $prefix
     * @return int
     */
    public static function getGeneration(string $prefix): int
    {
        if (!self::isEnabled()) {
            return 0;
        }

        return (int) Cache::get(self::key(self::PREFIX_CACHE_GEN, $prefix), 0);
    }

    /**
     * Flush cache by tags (database/redis only).
     *
     * @deprecated Use invalidateByPrefix() instead — works with all drivers.
     * @param  array  $tags
     * @return bool
     */
    public static function flushTags(array $tags): bool
    {
        if (!self::isEnabled()) {
            return true;
        }

        if (config('cache.default') === 'database' && method_exists(Cache::store(), 'tags')) {
            return Cache::tags($tags)->flush();
        }

        return true;
    }

    /**
     * Check if caching is enabled.
     */
    protected static function isEnabled(): bool
    {
        $driver = config('cache.default', 'file');
        return $driver !== 'null' && $driver !== 'array';
    }

    /**
     * Build a namespaced cache key.
     */
    public static function key(string $prefix, string ...$parts): string
    {
        return implode(':', array_merge([$prefix], $parts));
    }
}