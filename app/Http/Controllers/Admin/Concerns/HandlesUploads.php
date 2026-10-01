<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesUploads
{
    /**
     * Persist an uploaded image on the public disk and return its relative path.
     */
    protected function storeImage(?UploadedFile $file, string $directory): ?string
    {
        return $file?->store($directory, 'public');
    }

    /**
     * Remove a previously stored image from the public disk.
     */
    protected function deleteImage(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
