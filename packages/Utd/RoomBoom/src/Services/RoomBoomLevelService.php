<?php

namespace Utd\RoomBoom\Services;

use Illuminate\Database\Eloquent\Collection;
use Utd\RoomBoom\Repositories\RoomBoomLevelRepository;

class RoomBoomLevelService
{
    public function __construct(private readonly RoomBoomLevelRepository $roomBoomLevelRepository) {}

    public function index($id): Collection|array
    {
        return $this->roomBoomLevelRepository->getLatestWithRewards($id);
    }

    public function getVideos(): \Illuminate\Support\Collection
    {
        return $this->roomBoomLevelRepository->getVideos();
    }
}
