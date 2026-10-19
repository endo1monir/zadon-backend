<?php

namespace App\Support;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Driver-agnostic replacement for Cache::tags().
 *
 * The file and database stores (used in production) do not support tagging,
 * so each group keeps a version number that is baked into its cache keys.
 * Flushing a group bumps the version, which orphans every old key; the
 * orphans expire on their own TTL.
 */
class CacheGroup
{
    public static function remember(
        string|array $groups,
        string $key,
        DateTimeInterface|DateInterval|int $ttl,
        Closure $callback,
    ): mixed {
        return Cache::remember(self::key($groups, $key), $ttl, $callback);
    }

    public static function flush(string|array $groups): void
    {
        foreach ((array) $groups as $group) {
            Cache::forever(self::versionKey($group), self::version($group) + 1);
        }
    }

    private static function key(string|array $groups, string $key): string
    {
        $versions = array_map(
            fn (string $group) => $group.':v'.self::version($group),
            (array) $groups,
        );

        return implode('|', $versions).'|'.$key;
    }

    private static function version(string $group): int
    {
        return (int) Cache::get(self::versionKey($group), 1);
    }

    private static function versionKey(string $group): string
    {
        return 'cache-group:'.$group.':version';
    }
}
