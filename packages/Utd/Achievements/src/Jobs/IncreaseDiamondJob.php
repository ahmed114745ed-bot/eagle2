<?php

namespace Utd\Achievements\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class IncreaseDiamondJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $userId, private float $value)
    {}

    public function handle(): void
    {
        \DB::transaction(fn() => incrementMonthlyDiamond($this->userId, $this->value));
    }
}
