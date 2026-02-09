<?php

namespace App\Traits\Gifts;

use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\FairLuckService;
use Illuminate\Support\Facades\App;

trait FairLuckIntegration
{
    /**
     * @var FairLuckService
     */
    protected $fairLuckService;

    /**
     * Resolve FairLuckService if not already resolved.
     */
    protected function getFairLuckService(): FairLuckService
    {
        if (!$this->fairLuckService) {
            $this->fairLuckService = App::make(FairLuckService::class);
        }
        return $this->fairLuckService;
    }

    /**
     * Process a bet using the Fair Luck Algorithm.
     */
    protected function processFairLuckBet(User $user, Gift $gift, float $totalGiftPrice, ?int $roomId = null)
    {
        return $this->getFairLuckService()->processBet(
            user: $user,
            gift: $gift,
            betAmount: $totalGiftPrice,
            roomId: $roomId
        );
    }
}
