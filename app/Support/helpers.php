<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('api_image')) {
    /**
     * Build the full public URL for a stored image path.
     */
    function api_image(?string $path, string $disk = 'public'): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk($disk)->url(ltrim($path, '/'));
    }
}
