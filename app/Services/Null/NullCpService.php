<?php

namespace App\Services\Null;

use App\Contracts\CpServiceContract;
use App\Models\User;
use Illuminate\Support\Collection;

class NullCpService implements CpServiceContract
{
    /**
     * Process CP when sending gift - returns empty array when package not installed
     */
    public function processCpWhenSendGift(User $sender, $receivers, int $giftId, int $giftPrice): array
    {
        return [];
    }

    /**
     * Upgrade CP level and experience - no-op when package not installed
     */
    public function upgradeLevelAndExp(?object $cp, $diamonds = null): bool
    {
        return false;
    }

    /**
     * Get eligible levels - returns empty collection when package not installed
     */
    public function getEligibleLevels(int $cpRelationId, int $totalCoins, int $currentLevelId)
    {
        return new Collection();
    }

    /**
     * Get current level - returns null when package not installed
     */
    public function getLevel(int $cpRelationId, int $totalCoins)
    {
        return null;
    }

    /**
     * Get all achieved levels - returns empty collection when package not installed
     */
    public function getLevels(int $cpRelationId, int $totalCoins)
    {
        return new Collection();
    }
}
