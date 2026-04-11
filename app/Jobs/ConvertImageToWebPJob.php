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
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

        $tempOutput = sys_get_temp_dir() . '/' . uniqid('webp_output_', true) . '.webp';

        try {
            // Get original image from storage
            $imageContent = $storage->get($this->originalPath);

            // Initialize Intervention Image Manager
            $manager = new ImageManager(new Driver());

            // Load and convert image to WebP using Intervention Image
            $image = $manager->read($imageContent);

            // Encode to WebP with quality setting
            $encoded = $image->toWebp($this->quality);

            // Save to temp file
            file_put_contents($tempOutput, $encoded);

            if (!file_exists($tempOutput)) {
                throw new \Exception('WebP conversion failed');
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
            // Cleanup temp file
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
