<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ChatMessageBatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Chat\Http\Repositories\ChatRepository;

class UpdateUserLastSeenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId
    ) {
        // Run on default queue to not block critical operations
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     *
     * This job handles the heavy operations that were previously blocking
     * the request cycle in UpdateLastSeen middleware:
     * 1. Get user's chat rooms
     * 2. Mark messages as received in batch
     * 3. Update user's last_seen_at timestamp
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);

            if (!$user) {
                Log::warning("UpdateUserLastSeenJob: User not found", ['user_id' => $this->userId]);
                return;
            }

            // Get user's chat rooms
            $chatsId = (new ChatRepository())->getUserChatRooms($user->id);

            // Mark messages as received in batch (if user has chat rooms)
            if (!empty($chatsId)) {
                app(ChatMessageBatchService::class)->markMessagesAsReceivedInBatch(
                    $chatsId,
                    $user->id
                );
            }

            // Update last_seen_at and online status
            $user->update([
                'last_seen_at' => now(),
                'online' => true,
            ]);

           
        } catch (\Throwable $e) {
            Log::error("UpdateUserLastSeenJob: Failed", [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("UpdateUserLastSeenJob: Failed permanently after {$this->tries} attempts", [
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }
}
