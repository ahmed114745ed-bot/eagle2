<?php

namespace Modules\RoomBoom\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\RoomBoom\Entities\RoomBoomLevel;

class RoomBoomLevelRepository
{
    public function getLatestWithRewards(): Collection|array
    {
        return RoomBoomLevel::with(['roomBoomRewards' => function ($query) {
            $query->orderBy('priority');
        }])->orderBy('level')->get();
    }
}
