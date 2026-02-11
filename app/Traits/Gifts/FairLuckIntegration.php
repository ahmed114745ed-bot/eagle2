<?php

namespace App\Traits\Gifts;

use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\FairLuckService3;
use Illuminate\Support\Facades\App;

trait FairLuckIntegration
{
    /**
     * @var FairLuckService3
     */
    protected $fairLuckService;

    /**
     * Resolve FairLuckService3 if not already resolved.
     */
    protected function getFairLuckService(): FairLuckService3
    {
        if (!$this->fairLuckService) {
            $this->fairLuckService = App::make(FairLuckService3::class);
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
