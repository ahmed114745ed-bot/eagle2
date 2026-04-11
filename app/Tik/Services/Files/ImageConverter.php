<?php

namespace App\Tik\Services\Files;

use App\Helpers\Common;
use App\Jobs\ConvertImageToWebPJob;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageConverter
{
    /**
     * Convert an uploaded image to WebP and upload directly to GCS.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string  $folder  Folder to save file on it (e.g., "images")
     * @param  int  $qScale  Quality scale (0–100, default 80)
     * @param  bool  $async  Whether to convert asynchronously using queue (default true)
     * @return string|false Public URL or false on failure
     */
    public static function toWebpAndUpload(UploadedFile $file, string $folder, int $qScale = 80, bool $async = true): false|string
    {
        if (! $file->isValid()) {
            return false;
        }

        // If async mode: upload original first, then convert in background
        if ($async) {
            return self::uploadAndConvertAsync($file, $folder, $qScale);
        }

        // Sync mode: convert first, then upload (original behavior)
        return self::convertAndUploadSync($file, $folder, $qScale);
    }

    /**
     * Upload original image first, then convert to WebP in background queue.
     */
    private static function uploadAndConvertAsync(UploadedFile $file, string $folder, int $qScale): false|string
    {
        // Upload original file immediately
        $path = Common::upload($folder, $file);

        if (!$path) {
            return false;
        }

        // Dispatch job to convert in background
        ConvertImageToWebPJob::dispatch($path, $folder, $qScale)
            ->onQueue('default'); // Use default queue for image processing

        // Return path immediately (will be converted to WebP in background)
        return $path;
    }

    /**
     * Convert to WebP synchronously, then upload (using Intervention Image).
     */
    private static function convertAndUploadSync(UploadedFile $file, string $folder, int $qScale): false|string
    {
        $tempOutput = sys_get_temp_dir().'/'.uniqid('webp_', true).'.webp';

        try {
            // Initialize Intervention Image Manager
            $manager = new ImageManager(new Driver());

            // Load and convert image to WebP
            $image = $manager->read($file->getPathname());

            // Encode to WebP with quality setting
            $encoded = $image->toWebp($qScale);

            // Save to temp file
            file_put_contents($tempOutput, $encoded);

            if (!file_exists($tempOutput)) {
                return false;
            }

            // Convert file path into UploadedFile
            $uploadedFile = new UploadedFile(
                $tempOutput,                     // Absolute path
                basename($tempOutput),           // Original file name
                'image/webp',                    // Mime type
                null,                            // Size (null = auto)
                true                             // Test mode (skip file upload checks)
            );

            $path = Common::upload($folder, $uploadedFile);

            // Delete temp file
            @unlink($tempOutput);

            // Return public URL
            return $path;

        } catch (\Exception $e) {
            @unlink($tempOutput);
            \Log::error('WebP conversion failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
