<?php

namespace Utd\Chat\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockUser extends Model
{
    use HasFactory;

    protected $fillable = ['blocker_id', 'blocked_id'];

    /**
     * Scope a query to filter block users between two specific users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId The ID of the first user.
     * @param int $fromUserId The ID of the second user.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenUsers($query, $userId, $fromUserId)
    {
        return $query->where(function ($q) use ($userId, $fromUserId) {
            $q->where(function ($subQuery) use ($userId, $fromUserId) {
                $subQuery->where('blocker_id', $userId)
                    ->where('blocked_id', $fromUserId);
            })->orWhere(function ($subQuery) use ($userId, $fromUserId) {
                $subQuery->where('blocker_id', $fromUserId)
                    ->where('blocked_id', $userId);
            });
        });
    }
}
