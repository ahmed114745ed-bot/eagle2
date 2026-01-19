<?php

namespace Utd\Achievements\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CalculateAchievement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gift;
    protected $number;
    protected $room_owner;

    /**
     * Create a new job instance.
     */
    public function __construct($gift, $number, $room_owner)
    {
        $this->gift = $gift;
        $this->number = $number;
        $this->room_owner = $room_owner;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $achievementService = app(\App\Contracts\AchievementContract::class);
        
        if ($this->gift?->type == 5 && $this->gift?->achievement) {
            $achievementService->giftTarget($this->gift, $this->number);
        }

        $achievementService->roomTarget($this->room_owner, ($this->number * $this->gift?->price));
    }
}
