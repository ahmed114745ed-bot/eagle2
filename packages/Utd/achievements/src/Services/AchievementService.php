<?php

namespace Utd\Achievements\Services;

use App\Contracts\AchievementContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Utd\Achievements\Entities\Achievement;
use Utd\Achievements\Entities\AchievementLevel;
use Utd\Achievements\Entities\UserAchievement;
use Utd\Achievements\Entities\UserAchievementLevel;
use Utd\Achievements\Enums\AchievementType;
use Utd\Achievements\Support\AchievementHelper;

/**
 * Main Achievement Service Implementation
 *
 * This implements the BASE PROJECT's Contract!
 * The package provides the real implementation.
 */
class AchievementService implements AchievementContract
{
    // =============================================
    // User-facing methods (API)
    // =============================================

    public function getUserAchievements(Model $user): Collection
    {
        return UserAchievementLevel::query()
            ->where('user_id', $user->id)
            ->where('is_enable', true)
            ->where('picked', true)
            ->with('achievementLevel.achievement')
            ->get();
    }

    public function getUserMedals(Model $user): Collection
    {
        return UserAchievementLevel::query()
            ->where('user_id', $user->id)
            ->where('is_enable', true)
            ->get();
    }

    public function trackCharging(Model $user, int $totalCoins): void
    {
        $achievement = Achievement::query()
            ->where('type', AchievementType::RECHARGE_TARGET)
            ->first();

        if (!$achievement) {
            return;
        }

        $this->updateOrCreateUserAchievement($user, $achievement, $totalCoins);
    }

    public function trackRoomTarget(Model $user, int $totalCoins): void
    {
        $achievement = Achievement::query()
            ->where('type', AchievementType::ROOM_TARGET)
            ->first();

        if (!$achievement) {
            return;
        }

        $this->updateOrCreateUserAchievement($user, $achievement, $totalCoins);
    }

    public function trackGiftTarget(Model $gift, int $total): void
    {
        $achievement = Achievement::query()
            ->where('type', AchievementType::GIFT_TARGET)
            ->first();

        if (!$achievement || !method_exists($gift, 'achievement')) {
            return;
        }

        $giftAchievement = $gift->achievement;
        if (!$giftAchievement) {
            return;
        }

        $user = $giftAchievement->user;
        $this->updateOrCreateUserAchievement($user, $achievement, $total, $giftAchievement->id);
    }

    // =============================================
    // Admin methods
    // =============================================

    public function all(): Collection
    {
        return Achievement::all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Achievement::query()
            ->with('levels')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function create(array $data): ?Model
    {
        return Achievement::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $achievement = Achievement::find($id);
        if (!$achievement) {
            return false;
        }
        return $achievement->update($data);
    }

    public function delete(int $id): bool
    {
        $achievement = Achievement::find($id);
        if (!$achievement) {
            return false;
        }
        return $achievement->delete();
    }

    public function find(int $id): ?Model
    {
        return Achievement::with('levels')->find($id);
    }

    // =============================================
    // Achievement Levels
    // =============================================

    public function getAllLevels(int $achievementId, int $perPage = 15): LengthAwarePaginator
    {
        return AchievementLevel::query()
            ->where('achievement_id', $achievementId)
            ->orderBy('target')
            ->paginate($perPage);
    }

    public function createLevel(array $data): ?Model
    {
        // Handle image uploads
        if (isset($data['valid_image']) && $data['valid_image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['valid_image'] = AchievementHelper::upload('achievements', $data['valid_image']);
        }
        if (isset($data['invalid_image']) && $data['invalid_image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['invalid_image'] = AchievementHelper::upload('achievements', $data['invalid_image']);
        }

        return AchievementLevel::create($data);
    }

    public function updateLevel(int $id, array $data): bool
    {
        $level = AchievementLevel::find($id);
        if (!$level) {
            return false;
        }

        // Handle image uploads
        if (isset($data['valid_image']) && $data['valid_image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['valid_image'] = AchievementHelper::upload('achievements', $data['valid_image']);
        }
        if (isset($data['invalid_image']) && $data['invalid_image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['invalid_image'] = AchievementHelper::upload('achievements', $data['invalid_image']);
        }

        return $level->update($data);
    }

    public function deleteLevel(int $id): bool
    {
        $level = AchievementLevel::find($id);
        if (!$level) {
            return false;
        }
        return $level->delete();
    }

    // =============================================
    // User Achievement Levels
    // =============================================

    public function getUserAchievementLevels(int $perPage = 15, ?string $uuid = null): LengthAwarePaginator
    {
        $query = UserAchievementLevel::query()
            ->with(['user', 'achievementLevel', 'achievement']);

        if ($uuid) {
            $userModel = config('achievements.models.user');
            $user = $userModel::where('uuid', $uuid)->first();
            if ($user) {
                $query->where('user_id', $user->id);
            }
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    public function toggleUserAchievementLevel(int $id, bool $isEnabled): bool
    {
        $userLevel = UserAchievementLevel::find($id);
        if (!$userLevel) {
            return false;
        }
        return $userLevel->update(['is_enable' => $isEnabled]);
    }

    public function deleteUserAchievementLevel(int $id): bool
    {
        $userLevel = UserAchievementLevel::find($id);
        if (!$userLevel) {
            return false;
        }
        return $userLevel->delete();
    }

    // =============================================
    // Status
    // =============================================

    public function isEnabled(): bool
    {
        return true; // Package is installed and working
    }

    // =============================================
    // Helper methods
    // =============================================

    protected function updateOrCreateUserAchievement(
        Model $user,
        Achievement $achievement,
        int $amount,
        ?int $giftAchievementId = null
    ): void {
        $userAchievement = UserAchievement::query()
            ->where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->where('gift_achievement_id', $giftAchievementId)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->first();

        if (!$userAchievement) {
            $totalTarget = UserAchievement::query()
                ->where('gift_achievement_id', $giftAchievementId)
                ->where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->max('total_target') ?? 0;

            $userAchievement = UserAchievement::create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'gift_achievement_id' => $giftAchievementId,
                'target' => $amount,
                'total_target' => $totalTarget + $amount,
                'month' => now()->month,
                'year' => now()->year,
            ]);
        } else {
            $userAchievement->target += $amount;
            $userAchievement->total_target += $amount;
            $userAchievement->save();
        }

        // Assign achievement level to user
        app(AchievementLevelService::class)->assignAchievementToUser($userAchievement);
    }

    public function getEnabledMedals(int $userId): array
    {
        return UserAchievementLevel::query()
            ->where('user_id', $userId)
            ->where('is_enable', true)
            ->get();
    }

    public function calculateAchievement(int $userId, string $type, float $amount): void
    {
        // TODO: Implement calculateAchievement() method.
    }

    public function hasAchievement(int $userId, int $achievementId): bool
    {
        // TODO: Implement hasAchievement() method.
    }

    public function getStatistics(int $userId): array
    {
        // TODO: Implement getStatistics() method.
    }
}
