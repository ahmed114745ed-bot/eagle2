<?php

namespace Utd\Achievements\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Contract for Achievement Service
 *
 * This interface defines the main operations for the Achievement system.
 * The base project should depend on this contract, not the concrete implementation.
 */
interface AchievementServiceInterface
{
    /**
     * Get all achievements for a user
     */
    public function getUserAchievements(Model $user): Collection;

    /**
     * Get user's enabled medals/badges
     */
    public function getUserMedals(Model $user): Collection;

    /**
     * Track charging/recharge achievement
     */
    public function trackCharging(Model $user, int $totalCoins): void;

    /**
     * Track room target achievement
     */
    public function trackRoomTarget(Model $user, int $totalCoins): void;

    /**
     * Track gift target achievement
     */
    public function trackGiftTarget(Model $gift, int $total): void;

    /**
     * Check if achievement system is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get all achievements
     */
    public function all(): Collection;
}
