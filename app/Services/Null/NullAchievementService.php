<?php
namespace App\Services\Null;

use App\Contracts\AchievementContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Utd\Achievements\Entities\Achievement;

class NullAchievementService implements AchievementContract
{
    public function getUserAchievements(Model $user): Collection
    {
        return collect();
    }

    public function getUserMedals(Model $user): Collection
    {
        return collect();
    }

    public function trackCharging(Model $user, int $totalCoins): void
    {
        // No-op
    }

    public function trackRoomTarget(Model $user, int $totalCoins): void
    {
        // No-op
    }

    public function trackGiftTarget(Model $gift, int $total): void
    {
        // No-op
    }

    public function all(): Collection
    {
        return collect();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $perPage);
    }

    public function create(array $data): ?Model
    {
        return null;
    }

    public function update(int $id, array $data): bool
    {
        return false;
    }

    public function delete(int $id): bool
    {
        return false;
    }

    public function find(int $id): ?Model
    {
        return null;
    }

    public function getAllLevels(int $achievementId, int $perPage = 15): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $perPage);
    }

    public function createLevel(array $data): ?Model
    {
        return null;
    }

    public function updateLevel(int $id, array $data): bool
    {
        return false;
    }

    public function deleteLevel(int $id): bool
    {
        return false;
    }

    public function getUserAchievementLevels(int $perPage = 15, ?string $uuid = null): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $perPage);
    }

    public function toggleUserAchievementLevel(int $id, bool $isEnabled): bool
    {
        return false;
    }

    public function deleteUserAchievementLevel(int $id): bool
    {
        return false;
    }

    public function isEnabled(): bool
    {
        return false;
    }

    public function updateOrCreateUserAchievement(Model $user, Achievement $achievement, int $amount, ?int $giftAchievementId = null): void
    {
        // No-op
    }
}
