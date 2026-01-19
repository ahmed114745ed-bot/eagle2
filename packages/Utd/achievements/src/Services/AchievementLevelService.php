<?php

namespace Utd\Achievements\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Utd\Achievements\Entities\AchievementLevel;
use Utd\Achievements\Entities\UserAchievement;
use Utd\Achievements\Entities\UserAchievementLevel;
use Utd\Achievements\Enums\TargetType;

class AchievementLevelService
{
    private Collection $userAchievementLevels;
    private int $countTargets = 2;

    /**
     * Assign achievement to user
     */
    public function assignAchievementToUser(?UserAchievement $userAchievement): void
    {
        if (!$userAchievement) return;

        $notificationIds = $this->approveAchievement($userAchievement);
        $this->sendAchievementNotifications($notificationIds);
    }

    /**
     * Approve achievement and get notification IDs
     */
    public function approveAchievement(?UserAchievement $userAchievement, ?array $notificationIds = null): array
    {
        if ($notificationIds == null) {
            $notificationIds = array_fill(0, $this->countTargets, []);
        }

        if (!$userAchievement) return $notificationIds;

        $targetType = $this->getAchievement($userAchievement);

        if ($targetType) {
            switch ($targetType) {
                case TargetType::MONTHLY:
                    $notificationIds[0][] = @$userAchievement->user->notification_id;
                    break;
                case TargetType::DEFAULT:
                    $notificationIds[1][] = @$userAchievement->user->notification_id;
                    break;
                default:
                    break;
            }
        }

        return $notificationIds;
    }

    /**
     * Get achievement for user
     */
    public function getAchievement(UserAchievement $userAchievement): ?TargetType
    {
        $achievement = $userAchievement->achievement;
        $achievementLevels = $achievement->levels;
        $levelIds = $this->getLevelsIds($achievementLevels);
        $defaultIds = $this->getDefaultLevelsIds($achievementLevels);

        $currentTarget = $userAchievement->target;
        $totalTarget = $userAchievement->total_target;

        $achievementLevel = $achievementLevels
            ->where('target', '<=', $totalTarget)
            ->where('target_type', TargetType::DEFAULT)
            ->sortByDesc('target')
            ->first();

        if ($achievementLevel == null) {
            $achievementLevel = $achievementLevels
                ->where('target', '<=', $currentTarget)
                ->where('target_type', '!=', TargetType::DEFAULT)
                ->sortByDesc('target')
                ->first();
        }

        $user = $userAchievement->user;
        $userId = $user->id;
        $this->userAchievementLevels = $this->getUserAchievementLevels($userId, $levelIds);

        if ($achievementLevel != null) {
            $ifGreaterThan = $this->checkIfComingLevelGreaterThanExists(
                $achievementLevel->id,
                $userId,
                $levelIds
            );

            if ($ifGreaterThan) {
                try {
                    $this->assignAchievement(
                        $userId,
                        $achievementLevel,
                        $levelIds,
                        $defaultIds,
                        @$userAchievement?->gift_achievement_id
                    );
                } catch (\Exception $e) {
                    return null;
                }
                return $achievementLevel->target_type;
            }
        }

        return null;
    }

    /**
     * Get sorted level IDs
     */
    public function getLevelsIds($achievementLevels): array
    {
        return $achievementLevels->sortBy('target')->pluck('id')->toArray();
    }

    /**
     * Get default level IDs
     */
    public function getDefaultLevelsIds($achievementLevels): array
    {
        return $achievementLevels
            ->where('target_type', TargetType::DEFAULT)
            ->sortBy('target')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get user achievement levels
     */
    public function getUserAchievementLevels(int $userId, array $levelIds): array|Collection
    {
        return UserAchievementLevel::query()
            ->where('user_id', $userId)
            ->where('is_enable', true)
            ->whereIn('achievement_level_id', $levelIds)
            ->where(function ($query) {
                $query->where('end_at', '>=', today())->orWhere('end_at', null);
            })
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Check if coming level is greater than exists
     */
    private function checkIfComingLevelGreaterThanExists(int $targetId, int $userId, array $levelIds): bool
    {
        if (!isset($this->userAchievementLevels)) {
            $this->userAchievementLevels = $this->getUserAchievementLevels($userId, $levelIds);
        }

        $userAchievement = $this->userAchievementLevels
            ->sortBy(fn($item) => array_search($item['achievement_level_id'], $levelIds))
            ->last();

        if ($userId != null) {
            $data = $this->checkIfLevelExpiredInMonth($userId, $targetId);
            if ($data) {
                return false;
            }
        }

        return ($userAchievement == null) ||
            $this->isGreater($userAchievement->achievement_level_id, $levelIds, $targetId);
    }

    /**
     * Check if level is greater
     */
    public function isGreater($achievementLevelId, array $levelIds, int $targetId): bool
    {
        $prevIndex = array_search($achievementLevelId, $levelIds);
        $currentIndex = array_search($targetId, $levelIds);

        return $currentIndex === false || ($currentIndex > $prevIndex);
    }

    /**
     * Assign achievement to user
     */
    public function assignAchievement(
        int $userId,
        AchievementLevel $achievementLevel,
        array $levelIds,
        array $defaultIds,
        $giftId = null
    ): void {
        if (!isset($this->userAchievementLevels)) {
            $this->userAchievementLevels = $this->getUserAchievementLevels($userId, $levelIds);
        }

        $userAchievementAll = UserAchievementLevel::where("user_id", $userId)
            ->where(fn($q) => $q->where('end_at', '>=', today())->orWhere('end_at', null))
            ->where('achievement_level_id', '!=', null)
            ->pluck("achievement_level_id")
            ->toArray();

        $all_achievements = AchievementLevel::where("target", '<=', $achievementLevel->target)
            ->where("target_type", $achievementLevel->target_type)
            ->where("achievement_id", $achievementLevel->achievement_id)
            ->whereNotIn("id", $userAchievementAll)
            ->get();

        foreach ($all_achievements as $level) {
            $attributes = [
                'user_id' => $userId,
                'achievement_level_id' => $level->id,
                'gift_achievement_id' => $giftId,
            ];
            $this->appendEndAt($attributes, $level->target_type);

            UserAchievementLevel::query()->create($attributes);
        }
    }

    /**
     * Append end date based on target type
     */
    private function appendEndAt(array &$attributes, TargetType $targetType): void
    {
        $attributes['end_at'] = match ($targetType) {
            TargetType::MONTHLY => today()->addDays(30),
            TargetType::WEEKLY => today()->addDays(7),
            default => null,
        };
    }

    /**
     * Send achievement notifications
     */
    public function sendAchievementNotifications(?array $notificationIds): void
    {
        if ($notificationIds == null || count($notificationIds) < $this->countTargets) return;

        // Use helper if available, otherwise use custom notification logic
        if (function_exists('send_firebase_notification')) {
            if (count($notificationIds[0]) > 0) {
                send_firebase_notification(
                    $notificationIds[0],
                    config('app.name_en', config('app.name')),
                    'Congratulation you achieve new monthly achievement ends at ' . Carbon::now()->endOfMonth()->shortAbsoluteDiffForHumans()
                );
            }

            if (count($notificationIds[1]) > 0) {
                send_firebase_notification(
                    $notificationIds[1],
                    config('app.name_ar', config('app.name')),
                    'تهانينا لقد ربحت وسام جديد دائم 🥇'
                );
            }
        }
    }

    /**
     * Set user achievement levels (scheduled task)
     */
    public function setUserAchievementLevel(bool $isTest = false): void
    {
        if (!$isTest) {
            $lastMonth = now()->subMonth()->month;
            $lastYear = now()->subMonth()->year;
        } else {
            $lastMonth = now()->month;
            $lastYear = now()->year;
        }

        $userAchievements = UserAchievement::query()
            ->selectRaw('target, total_target, user_id, achievement_id')
            ->where('month', $lastMonth)
            ->where('year', $lastYear)
            ->with([
                'achievement.levels',
                'user' => fn($query) => $query->withoutAppends()->select(['id', 'name', 'notification_id'])
            ])
            ->get()
            ->chunk(1000);

        foreach ($userAchievements as $userAchievementChunks) {
            $notificationIds = null;
            foreach ($userAchievementChunks as $userAchievement) {
                $notificationIds = $this->approveAchievement($userAchievement, $notificationIds);
            }
            $this->sendAchievementNotifications($notificationIds);
        }
    }

    /**
     * Assign achievement level to user by admin
     */
    public function assignAchievementLevelToUserByAdmin(int $userId, AchievementLevel $achievementLevel): bool
    {
        $achievement = $achievementLevel->achievement;
        $achievementLevels = $achievement->levels;
        $levelIds = $this->getLevelsIds($achievementLevels);
        $defaultIds = $this->getDefaultLevelsIds($achievementLevels);

        $ifGreaterThan = $this->checkIfComingLevelGreaterThanExists($achievementLevel->id, $userId, $levelIds);

        if ($ifGreaterThan) {
            try {
                $this->assignAchievement($userId, $achievementLevel, $levelIds, $defaultIds);
            } catch (\Exception $e) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Check if level expired in month
     */
    private function checkIfLevelExpiredInMonth(int $userId, int $levelId): bool
    {
        return UserAchievementLevel::query()
            ->whereMonth("created_at", date("m"))
            ->whereYear("created_at", date("Y"))
            ->where('user_id', $userId)
            ->where('is_enable', false)
            ->where('achievement_level_id', $levelId)
            ->exists();
    }
}
