<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Utd\Achievements\Entities\Achievement;

interface AchievementContract
{
    public function getUserAchievements(Model $user): Collection;

    public function getUserMedals(Model $user): Collection;

    public function trackCharging(Model $user, int $totalCoins): void;

    public function trackRoomTarget(Model $user, int $totalCoins): void;

    public function trackGiftTarget(Model $gift, int $total): void;

    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): ?Model;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function find(int $id): ?Model;

    public function getAllLevels(int $achievementId, int $perPage = 15): LengthAwarePaginator;

    public function createLevel(array $data): ?Model;

    public function updateLevel(int $id, array $data): bool;

    public function deleteLevel(int $id): bool;

    public function getUserAchievementLevels(int $perPage = 15, ?string $uuid = null): LengthAwarePaginator;

    public function toggleUserAchievementLevel(int $id, bool $isEnabled): bool;

    public function deleteUserAchievementLevel(int $id): bool;

    public function isEnabled(): bool;

    function updateOrCreateUserAchievement(Model $user, Achievement $achievement, int $amount, ?int $giftAchievementId = null): void;
}
