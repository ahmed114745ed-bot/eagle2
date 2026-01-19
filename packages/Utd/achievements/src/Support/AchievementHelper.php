<?php

namespace Utd\Achievements\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Helper class for Achievement Package
 *
 * Standalone utilities - no dependency on App\Helpers\Common
 */
class AchievementHelper
{
    /**
     * Upload a file to storage
     */
    public static function upload(string $folder, UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        return Storage::url($path);
    }

    /**
     * Delete a file from storage
     */
    public static function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    /**
     * Get full URL for a stored file
     */
    public static function getUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::url($path);
    }
}
