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
            // Initialize Intervention Image Manager with GD driver
            $manager = new ImageManager(new Driver());

            // Read image from uploaded file path (not binary data)
            $image = $manager->read($file->getPathname());

            // Encode to WebP with quality setting
            $encoded = $image->toWebp($qScale);

            // Save to temp file
            file_put_contents($tempOutput, $encoded->toString());

            if (!file_exists($tempOutput)) {
                \Log::error('WebP sync conversion failed - output file not created', [
                    'file' => $file->getClientOriginalName(),
                ]);
                return false;
            }

            // Prepare filename
            $pathInfo = pathinfo($file->getClientOriginalName());
            $webpFilename = $pathInfo['filename'] . '.webp';

            // Convert file path into UploadedFile
            $uploadedFile = new UploadedFile(
                $tempOutput,
                $webpFilename,
                'image/webp',
                null,
                true
            );

            $path = Common::upload($folder, $uploadedFile);

            // Delete temp file
            @unlink($tempOutput);

            // Return public URL
            return $path;

        } catch (\Exception $e) {
            @unlink($tempOutput);
            \Log::error('WebP sync conversion failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
