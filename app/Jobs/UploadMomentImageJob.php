<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Models\MomentGallery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Moment\Entities\Moment;

class CreateMomentWithImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [10, 30, 60, 120];

    protected $userId;
    protected $description;
    protected $tempFiles;

    public function __construct($userId, $description, array $tempFiles = [])
    {
        $this->userId = $userId;
        $this->description = $description;
        $this->tempFiles = $tempFiles;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        Log::info('CreateMomentWithImagesJob attempt', [
            'user_id' => $this->userId,
            'attempt' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);

        DB::beginTransaction();

        try {

            $moment = Moment::create([
                'user_id' => $this->userId,
                'description' => $this->description,
            ]);

            foreach ($this->tempFiles as $tempPath) {

                $fullPath = storage_path('app/' . $tempPath);

                if (!file_exists($fullPath)) {
                    throw new \Exception("Temp file not found: {$fullPath}");
                }

                $file = new UploadedFile(
                    $fullPath,
                    basename($fullPath),
                    null,
                    null,
                    true
                );

                $path = Common::upload('profile', $file);

                MomentGallery::create([
                    'moment_id' => $moment->id,
                    'image' => $path,
                ]);

                @unlink($fullPath);
            }

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            foreach ($this->tempFiles as $tempPath) {
                @unlink(storage_path('app/' . $tempPath));
            }

            Log::error('CreateMomentWithImagesJob failed', [
                'user_id' => $this->userId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CreateMomentWithImagesJob permanently failed', [
            'user_id' => $this->userId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }
}
