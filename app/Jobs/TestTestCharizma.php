<?php

namespace App\Jobs;

use App\Helpers\Common;
use GuzzleHttp\Promise\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Classes\Gifts\CharismaWork;
use App\Classes\Gifts\PKWork;

class TestTestCharizma implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes max
    public $tries = 3; // Maximum 3 attempts
    public $backoff = [30, 60]; // Retry after 30s, then 60s

    private array $data;
    private string $jobType; // Store type instead of interface

    /**
     * Create a new job instance.
     */
    public function __construct($data, $roomJob)
    {
        $this->data = is_array($data) ? $data : [];
        // Store the type name instead of the interface instance to avoid serialization issues
        $this->jobType = $roomJob instanceof CharismaWork ? 'charisma' : 'pk';
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Recreate the roomJob instance based on stored type
            $roomJob = $this->jobType === 'charisma' ? new CharismaWork() : new PKWork();

            $promises = [];

            if (!is_array($this->data) || empty($this->data)) {
                Log::warning('TestTestCharizma: Empty or invalid data', [
                    'job_type' => $this->jobType,
                    'data' => $this->data
                ]);
                return;
            }

            foreach ($this->data as $value) {
                try {
                    [$data, $roomId, $userId] = $roomJob->getVariables($value);

                    $json = $roomJob->sendToZego($data, $roomId, $userId ?? 0);

                    $promise = Common::sendToZego3('SendCustomCommand', $roomId, $userId ?? 0, [$json]);
                    $promises = array_merge($promises, $promise);
                } catch (\Throwable $e) {
                    Log::error('TestTestCharizma: Failed to process item', [
                        'job_type' => $this->jobType,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue processing other items
                    continue;
                }
            }

            if (!empty($promises)) {
                Utils::unwrap($promises);
            }
        } catch (\Throwable $e) {
            Log::error('TestTestCharizma: Job failed completely', [
                'job_type' => $this->jobType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // If we've tried 3 times, don't retry anymore
            if ($this->attempts() >= $this->tries) {
                Log::critical('TestTestCharizma: Job permanently failed after max attempts', [
                    'job_type' => $this->jobType,
                    'attempts' => $this->attempts()
                ]);
                return; // Don't throw - prevent infinite retries
            }

            throw $e; // Retry
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('TestTestCharizma: Job marked as failed', [
            'job_type' => $this->jobType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
