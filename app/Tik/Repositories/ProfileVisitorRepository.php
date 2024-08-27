<?php

namespace App\Tik\Repositories;

use App\Models\ProfileVisitor;

class ProfileVisitorRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new ProfileVisitor());
    }

    public function countUsersByYearAbdMonth($userIds)
    {
        return $this->model->query()->whereIn('user_id', $userIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
    }

    public function checkVisit($authId,$userId)
    {
        return $this->model->where('visitor_id', $authId)->where('user_id', $userId)
        ->whereDate('profile_visitors.created_at', today())->whereDate('profile_visitors.updated_at', today())
        ->exists();
    }
}
