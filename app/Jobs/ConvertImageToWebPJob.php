<?php

namespace App\Jobs;

use App\Helpers\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConvertImageToWebPJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [1, 2, 5];

    protected string $originalPath;
    protected string $folder;
    protected int $quality;
    protected ?string $disk;

    /**
     * Create a new job instance.
     *
     * @param string $originalPath Path to the original uploaded image on storage
     * @param string $folder Folder name (e.g., 'profile', 'banners')
     * @param int $quality WebP quality (0-100)
     * @param string|null $disk Storage disk name (null = default)
     */
    public function __construct(string $originalPath, string $folder, int $quality = 80, ?string $disk = null)
    {
        $this->originalPath = $originalPath;
        $this->folder = $folder;
        $this->quality = $quality;
        $this->disk = $disk ?? config('filesystems.default');
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        $storage = Storage::disk($this->disk);

        // Check if original file exists
        if (!$storage->exists($this->originalPath)) {
            Log::warning('ConvertImageToWebPJob: Original file not found', [
                'path' => $this->originalPath,
            ]);
            return;
        }

        // Download file to temp location
        $tempInput = sys_get_temp_dir() . '/' . uniqid('webp_input_', true) . '.tmp';
        $tempOutput = sys_get_temp_dir() . '/' . uniqid('webp_output_', true) . '.webp';

        try {
            // Download original from storage
            file_put_contents($tempInput, $storage->get($this->originalPath));

            // Run ffmpeg command
            $cmd = sprintf(
                'ffmpeg -y -i %s -c:v libwebp -lossless 0 -qscale %d -preset picture %s 2>&1',
                escapeshellarg($tempInput),
                $this->quality,
                escapeshellarg($tempOutput)
            );

            exec($cmd, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($tempOutput)) {
                throw new \Exception('FFmpeg conversion failed: ' . implode("\n", $output));
            }

            // Upload WebP version
            $uploadedFile = new UploadedFile(
                $tempOutput,
                basename($tempOutput),
                'image/webp',
                null,
                true
            );

            $webpPath = Common::upload($this->folder, $uploadedFile);

            if (!$webpPath) {
                throw new \Exception('Failed to upload WebP file');
            }

            // Delete original file and replace with WebP
            $storage->delete($this->originalPath);

            // If paths are different, move WebP to original path location
            if ($webpPath !== $this->originalPath) {
                // Copy WebP to original path with .webp extension
                $pathInfo = pathinfo($this->originalPath);
                $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';

                if ($storage->exists($webpPath)) {
                    $storage->move($webpPath, $newPath);
                }
            }

            Log::info('ConvertImageToWebPJob: Successfully converted image', [
                'original' => $this->originalPath,
                'webp' => $webpPath,
                'quality' => $this->quality,
            ]);

        } finally {
            // Cleanup temp files
            @unlink($tempInput);
            @unlink($tempOutput);
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ConvertImageToWebPJob failed permanently', [
            'original_path' => $this->originalPath,
            'folder' => $this->folder,
            'quality' => $this->quality,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        // Keep original file if conversion fails - better to have original than nothing
    }
}
