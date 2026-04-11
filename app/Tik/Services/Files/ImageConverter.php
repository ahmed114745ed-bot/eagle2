<?php

namespace App\Tik\Services\Files;

use App\Helpers\Common;
use App\Jobs\ConvertImageToWebPJob;
use Illuminate\Http\UploadedFile;

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
     * Convert to WebP synchronously, then upload (original behavior).
     */
    private static function convertAndUploadSync(UploadedFile $file, string $folder, int $qScale): false|string
    {
        $inputFile = $file->getPathname(); // Temporary uploaded file
        $tempOutput = sys_get_temp_dir().'/'.uniqid('webp_', true).'.webp';

        // Run ffmpeg command
        $cmd = sprintf(
            'ffmpeg -y -i %s -c:v libwebp -lossless 0 -qscale %d -preset picture %s 2>&1',
            escapeshellarg($inputFile),
            $qScale,
            escapeshellarg($tempOutput)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || ! file_exists($tempOutput)) {
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
    }
}
