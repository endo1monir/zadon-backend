<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Slug
{
    /**
     * Build a slug that is unique within the given table.
     *
     * Sources containing no transliterable characters (for example Arabic-only
     * names) fall back to the supplied default so a usable slug is always
     * produced.
     */
    public static function unique(
        string $source,
        string $table,
        ?int $ignoreId = null,
        string $fallback = 'item',
    ): string {
        $base = Str::slug($source);

        if ($base === '') {
            $base = $fallback;
        }

        $slug = $base;
        $suffix = 2;

        while (self::exists($table, $slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private static function exists(string $table, string $slug, ?int $ignoreId): bool
    {
        $query = DB::table($table)->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
