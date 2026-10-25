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

        $path = trim($path);

        // If it's already a full URL, extract the relative storage path if possible
        // and regenerate using the current app URL/disk config to avoid broken domains
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path);
            if (isset($parsed['path'])) {
                $storagePath = $parsed['path'];
                // Remove /storage/ prefix if present
                if (str_starts_with($storagePath, '/storage/')) {
                    $storagePath = ltrim(substr($storagePath, strlen('/storage/')), '/');
                    if ($storagePath === '') {
                        return $path;
                    }
                    return Storage::disk($disk)->url(ltrim($storagePath, '/'));
                }
                // If it's just a path without /storage/, try to use it as-is relative to disk
                if (! str_starts_with($storagePath, '/')) {
                    return Storage::disk($disk)->url(ltrim($storagePath, '/'));
                }
            }
            return $path;
        }

        return Storage::disk($disk)->url(ltrim($path, '/'));
    }
}
