<?php

namespace App\Tik\Services;

use Exception;
use App\Helpers\Common;
use App\Tik\Repositories\MusicRepository;



class MusicService
{
    public function __construct(private readonly MusicRepository $musicRepository,) {}


    public function all()
    {
        return $this->musicRepository->all();
    }

    public function userMusic($userId)
    {
        return $this->musicRepository->getByUser($userId);
    }

    public function destroyUserMusic($userId, $musicId)
    {
        return $this->musicRepository->deleteByUser($userId, $musicId);
    }
    public function create($userId, $url)
    {
        $data = [
            'user_id' => $userId,
            'url' => $url,
        ];

        $this->musicRepository->create($data);
        return true;
    }
}
