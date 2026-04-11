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

        $tempInput = sys_get_temp_dir() . '/' . uniqid('webp_input_', true);
        $tempOutput = sys_get_temp_dir() . '/' . uniqid('webp_output_', true) . '.webp';

        try {
            // Download original file to temp location first
            // This avoids binary corruption issues with Redis queue serialization
            file_put_contents($tempInput, $storage->get($this->originalPath));

            if (!file_exists($tempInput)) {
                throw new \Exception('Failed to download original file from storage');
            }

            // Initialize Intervention Image Manager with GD driver
            $manager = new ImageManager(new Driver());

            // Read image from temp file (not from binary data)
            $image = $manager->read($tempInput);

            // Encode to WebP with quality setting
            $encoded = $image->toWebp($this->quality);

            // Save WebP to temp file
            file_put_contents($tempOutput, $encoded->toString());

            if (!file_exists($tempOutput)) {
                throw new \Exception('WebP conversion failed - output file not created');
            }

            // Prepare for upload
            $pathInfo = pathinfo($this->originalPath);
            $webpFilename = $pathInfo['filename'] . '.webp';

            // Upload WebP version
            $uploadedFile = new UploadedFile(
                $tempOutput,
                $webpFilename,
                'image/webp',
                null,
                true
            );

            $webpPath = Common::upload($this->folder, $uploadedFile);

            if (!$webpPath) {
                throw new \Exception('Failed to upload WebP file to storage');
            }

            // Delete original file (already converted to WebP)
            $storage->delete($this->originalPath);

            Log::info('ConvertImageToWebPJob: Successfully converted image', [
                'original' => $this->originalPath,
                'webp' => $webpPath,
                'quality' => $this->quality,
                'size_before' => filesize($tempInput),
                'size_after' => filesize($tempOutput),
            ]);

        } catch (\Exception $e) {
            Log::error('ConvertImageToWebPJob: Conversion failed', [
                'path' => $this->originalPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;

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
