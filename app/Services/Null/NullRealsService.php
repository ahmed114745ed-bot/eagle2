<?php

namespace App\Services\Null;

use App\Contracts\RealsContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NullRealsService implements RealsContract
{
  
    public function getUserReals(int $userId, int $currentUserId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->emptyPaginator($perPage);
    }

 
    public function getFollowingReals(int $userId, int $perPage = 10): Collection|LengthAwarePaginator
    {
        return collect([]);
    }


    public function getAllReals(int $userId, ?string $filter = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->emptyPaginator($perPage);
    }

    public function getRealById(int $realId, int $currentUserId): ?array
    {
        return null;
    }

 
    public function createReal(int $userId, array $data): ?array
    {
        return null; 
    }

  
    public function updateReal(int $realId, array $data): bool
    {
        return false;
    }

    public function deleteReal(int $realId): bool
    {
        return false;
    }

  
    public function toggleLike(int $realId, int $userId): array
    {
        return [
            'success' => false,
            'message' => 'Feature not available',
            'liked' => false,
            'likes_count' => 0,
        ];
    }

   
    public function addComment(int $realId, int $userId, string $content): ?array
    {
        return null;
    }

  
    public function deleteComment(int $commentId, int $userId): bool
    {
        return false;
    }

   
    public function getComments(int $realId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->emptyPaginator($perPage);
    }

    public function getLikes(int $realId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->emptyPaginator($perPage);
    }

    public function recordView(int $realId, int $userId): void
    {
    }

 
    public function getUserRealsCount(int $userId): int
    {
        return 0;
    }


    public function reportReal(int $realId, int $userId, string $reason): bool
    {
        return false;
    }

  
    public function isFeatureAvailable(): bool
    {
        return false;
    }

    protected function emptyPaginator(int $perPage = 10): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: $perPage,
            currentPage: 1,
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
