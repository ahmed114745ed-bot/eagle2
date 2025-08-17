<?php

namespace Modules\RoomBoom\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\RoomBoom\Repositories\RoomBoomLevelRepository;

class RoomBoomLevelService
{
    public function __construct(private readonly RoomBoomLevelRepository $roomBoomLevelRepository)
    {
    }

    public function index($id): Collection|array
    {
        return $this->roomBoomLevelRepository->getLatestWithRewards($id);
    }

    public function getVideos(): Collection|array
    {
        return $this->roomBoomLevelRepository->getVideos();
    }
}
