<?php

namespace Utd\Achievements\Services;

use App\Contracts\AchievementLevelContract;
use App\Helpers\Common;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Utd\Achievements\Entities\AchievementLevel;
use Utd\Achievements\Entities\UserAchievement;
use Utd\Achievements\Entities\UserAchievementLevel;
use Utd\Achievements\Enums\TargetType;

class AchievementLevelsService implements AchievementLevelContract
{
    private Collection $userAchievementLevels;
    private int $countTargets = 2;

    public function __construct()
    {
    }

    /**
     * @param Model|null $userAchievement
     * @return void
     */
    public function assignAchievementToUser(?Model $userAchievement): void
    {
        if (!$userAchievement) return;

        $notificationIds = $this->approveAchievement($userAchievement);

        $this->sendAchievementNotifications($notificationIds);
    }

    /**
     * @param Model|null $userAchievement
     * @param array|null $notificationIds
     * @return array
     */
    public function approveAchievement(?Model $userAchievement, ?array $notificationIds = null): array
    {

        if ($notificationIds == null) $notificationIds = array_fill(0, $this->countTargets, []);

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
     * @param Model|null $userAchievement
     * @return TargetType|null
     * @throws \Throwable
     */
    public function getAchievement(?Model $userAchievement): ?TargetType
    {
        $achievement = $userAchievement->achievement;
        $achievementLevels = $achievement->levels;
        $levelIds = $this->getLevelsIds($achievementLevels);
        $defaultIds = $this->getDefaultLevelsIds($achievementLevels);

        $currentTarget = $userAchievement->target;
        $totalTarget = $userAchievement->total_target;


        $achievementLevel =
            $achievementLevels->where('target', '<=', $totalTarget)->where('target_type', TargetType::DEFAULT)->sortByDesc('target')->first();
        if ($achievementLevel == null) {
            $achievementLevel =
                $achievementLevels->where('target', '<=', $currentTarget)->where('target_type', '!=', TargetType::DEFAULT)->sortByDesc('target')->first();
        }

        $user = $userAchievement->user;
        $userId = $user->id;
        $this->userAchievementLevels = $this->getUserAchievementLevels($userId, $levelIds);

        if ($achievementLevel != null) {

            $ifGreaterThan =
                $this->checkIfComingLevelGreaterThanExists($achievementLevel->id, $userId, $levelIds, ($userAchievement->user_id == 43 && $userAchievement->achievement_id == 2));

            if ($ifGreaterThan) {
                try {
                    $this->assignAchievement($userId, $achievementLevel, $levelIds, $defaultIds, @$userAchievement?->gift_achievement_id);

                } catch (\Exception $e) {
                    info($e->getMessage());
                    return null;
                }
                return $achievementLevel->target_type;
            }
        }

        return null;
    }

    /**
     * @param $achievementLevels
     * @return mixed
     */
    public function getLevelsIds($achievementLevels): mixed
    {
        return $achievementLevels->sortBy('target')->pluck('id')->toArray();
    }

    /**
     * @param $achievementLevels
     * @return mixed
     */
    public function getDefaultLevelsIds($achievementLevels): mixed
    {
        return $achievementLevels->where('target_type', TargetType::DEFAULT)->sortBy('target')->pluck('id')->toArray();
    }

    /**
     * @param int $userId
     * @param array $levelIds
     * @return Builder[]|Collection
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
            ->orderByDesc('id')->get();
    }

    /**
     * @param int $targetId
     * @param int $userId
     * @param array $levelIds is a sorted levels
     * @return bool
     */
    private function checkIfComingLevelGreaterThanExists(int $targetId, int $userId, array $levelIds, bool $isTest = false): bool
    {
        if (!isset($this->userAchievementLevels)) {
            $this->userAchievementLevels = $this->getUserAchievementLevels($userId, $levelIds);
        }

        $userAchievement = $this->userAchievementLevels->sortBy(function ($item) use ($levelIds) {
            return array_search($item['achievement_level_id'], $levelIds);
        })->last();

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
     * @param $achievementLevelId
     * @param array $levelIds
     * @param int $targetId
     * @return bool
     */
    public function isGreater($achievementLevelId, array $levelIds, int $targetId): bool
    {
        $prevIndex = array_search($achievementLevelId, $levelIds);
        $currentIndex = array_search($targetId, $levelIds);

        return $currentIndex === false || ($currentIndex > $prevIndex);
    }

    /**
     * @throws \Throwable
     */
    public function assignAchievement(int $userId, AchievementLevel $achievementLevel, array $levelIds, array $defaultIds, $giftId = null): void
    {
        if (!isset($this->userAchievementLevels)) {
            $this->userAchievementLevels = $this->getUserAchievementLevels($userId, $levelIds);
        }

        $userAchievementAll = UserAchievementLevel::where("user_id", $userId)->where(fn($q) => $q->where('end_at', '>=', today())->orWhere('end_at', null))->where('achievement_level_id', '!=', null)->pluck("achievement_level_id")->toArray();
        $all_achievements = AchievementLevel::where("target", '<=', $achievementLevel->target)
            ->where("target_type", $achievementLevel->target_type)
            ->where("achievement_id", $achievementLevel->achievement_id)
            ->whereNotIn("id", $userAchievementAll)
            ->get();

        if ($all_achievements->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($all_achievements, $userId, $giftId) {
            foreach ($all_achievements as $achievementLevel) {
                $attributes = [
                    'user_id' => $userId,
                    'achievement_level_id' => $achievementLevel->id,
                    'gift_achievement_id' => $giftId,
                ];
                $this->appendEndAt($attributes, $achievementLevel->target_type);
                UserAchievementLevel::query()->create($attributes);
            }
        });
    }

    private function appendEndAt(array &$attributes, TargetType $targetType): void
    {
        $timezone = getTimezone();
        $attributes['end_at'] = match ($targetType) {
            TargetType::MONTHLY => today($timezone)->addDays(30),
            TargetType::WEEKLY => today($timezone)->addDays(7),
            default => null,
        };
    }

    /**
     * @param array|null $notificationIds
     * @return void
     */
    public function sendAchievementNotifications(?array $notificationIds): void
    {
        if ($notificationIds == null || count($notificationIds) < $this->countTargets) return;
        $timezone = getTimezone();

        if (count($notificationIds[0]) > 0) {
            Common::send_firebase_notification($notificationIds[0], config('app.name_en'), __('Congratulations! You achieved a new monthly achievement ending at') . ' ' . Carbon::now($timezone)->endOfMonth()->shortAbsoluteDiffForHumans());
        }

        if (count($notificationIds[1]) > 0) {
            Common::send_firebase_notification($notificationIds[1], config('app.name_ar'), __('Congratulations! You won a new permanent medal') . ' 🥇');
        }
    }

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

    public function getExpiredUserAchievementLevels(int $userId, int $levelId): array|Collection
    {
        return UserAchievementLevel::query()
            ->where([
                'user_id' => $userId,
                'is_enable' => false,
                'achievement_level_id' => $levelId
            ])
            ->orderByDesc('id')->get();
    }

    private function checkIfLevelExpired(int $levelId, int $userId): bool
    {
        return UserAchievementLevel::query()
            ->where([
                'user_id' => $userId,
                'is_enable' => false,
                'achievement_level_id' => $levelId
            ])->exists();
    }

    private function checkIfLevelExpiredInMonth(int $userId, int $levelId): bool
    {
        $timezone = getTimezone();
        $now = now($timezone);

        return UserAchievementLevel::query()
            ->whereMonth("created_at", $now->month)
            ->whereYear("created_at", $now->year)
            ->where('user_id', $userId)
            ->where('is_enable', false)
            ->where('achievement_level_id', $levelId)
            ->exists();
    }

}
