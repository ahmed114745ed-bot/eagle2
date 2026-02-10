<?php

namespace App\Contracts;

use App\Models\User;

interface CpServiceContract
{
    /**
     * Process CP when sending gift
     */
    public function processCpWhenSendGift(User $sender, $receivers, int $giftId, int $giftPrice): array;

    /**
     * Upgrade CP level and experience
     */
    public function upgradeLevelAndExp(?object $cp, $diamonds = null): bool;

    /**
     * Get eligible levels for CP
     */
    public function getEligibleLevels(int $cpRelationId, int $totalCoins, int $currentLevelId);

    /**
     * Get current level for CP
     */
    public function getLevel(int $cpRelationId, int $totalCoins);

    /**
     * Get all achieved levels for CP
     */
    public function getLevels(int $cpRelationId, int $totalCoins);
}
