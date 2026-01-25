<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


interface RealsContract
{
    
    public function getUserReals(int $userId, int $currentUserId, int $perPage = 10): LengthAwarePaginator;

  
    public function getFollowingReals(int $userId, int $perPage = 10): Collection|LengthAwarePaginator;

  
    public function getAllReals(int $userId, ?string $filter = null, int $perPage = 10): LengthAwarePaginator;

    
    public function getRealById(int $realId, int $currentUserId): ?array;

    
    public function createReal(int $userId, array $data): ?array;

    
    public function updateReal(int $realId, array $data): bool;

  
    public function deleteReal(int $realId): bool;

   
    public function toggleLike(int $realId, int $userId): array;

    
    public function addComment(int $realId, int $userId, string $content): ?array;

   
    public function deleteComment(int $commentId, int $userId): bool;

 
    public function getComments(int $realId, int $perPage = 20): LengthAwarePaginator;

   
    public function getLikes(int $realId, int $perPage = 20): LengthAwarePaginator;

  
    public function recordView(int $realId, int $userId): void;

   
    public function getUserRealsCount(int $userId): int;

   
    public function reportReal(int $realId, int $userId, string $reason): bool;

   
    public function isFeatureAvailable(): bool;
}
