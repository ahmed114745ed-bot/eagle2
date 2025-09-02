<?php

namespace App\Tik\Services\Files;

use App\Helpers\Common;
use Illuminate\Http\UploadedFile;

class ImageConverter
{
    /**
     * Convert an uploaded image to WebP and upload directly to GCS.
     *
     * @param  string  $folder  Folder to save file on it (e.g., "images")
     * @param  int  $qScale  Quality scale (0–100, default 80)
     * @return string|false Public URL or false on failure
     */
    public static function toWebpAndUpload(UploadedFile $file, string $folder, int $qScale = 80): false|string
    {
        if (! $file->isValid()) {
            return false;
        }

        $inputFile = $file->getPathname(); // Temporary uploaded file
        $tempOutput = sys_get_temp_dir().'/'.uniqid('webp_', true).'.webp';

        // Run ffmpeg command
        $cmd = sprintf(
            'ffmpeg -y -i %s -c:v libwebp -lossless 0 -qscale %d -preset picture %s',
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
        unlink($tempOutput);

        // Return public URL
        return $path;
    }
}
