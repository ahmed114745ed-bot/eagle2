<?php

namespace Utd\Family\Traits;

use Auth;
use Utd\Family\Entities\FamilyUser;

trait ResourceTrait
{
    public function amMember($userId = null): bool
    {
        if (! $userId) {
            $userId = Auth::id();
        }

        return $this->members()->where('user_id', $userId)->exists();
    }

    public function amOwner($userId = null): bool
    {
        if (! $userId) {
            $userId = Auth::id();
        }

        return @$this->user_id === $userId;
    }

    public function usersRequests()
    {
        return $this->hasManyThrough(
            family_model_or_fail('user'),
            FamilyUser::class,
            'family_id',
            'id',
            'id',
            'user_id'
        )->where('family_user.status', 0);
    }

    public function getUsersRequestsCountAttribute()
    {
        $usersRequestsCount = @$this->attributes['users_requests_count'];

        return ! is_null($usersRequestsCount) ? $usersRequestsCount : $this->usersRequests()->count();
    }
}
