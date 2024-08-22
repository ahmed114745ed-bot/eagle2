<?php

namespace App\Services;

use App\Repositories\AllGameRepository;
use App\Http\Resources\AllGameResource;
use App\Repositories\User\UserRepository;

class AllGameService
{
    protected $allGameRepository;
    protected $userRepository;

    public function __construct(AllGameRepository $allGameRepository,UserRepository $userRepository)
    {
        $this->allGameRepository = $allGameRepository;
        $this->userRepository = $userRepository;
    }

    public function getAllGamesData()
    {
        // Fetch full games
        $allGames = $this->allGameRepository->getAllEnabledGames();
        \request()->type = 0;
        $fullGames = AllGameResource::collection($allGames)->toArray(request());

        // Fetch mini games
        $miniGames = $this->allGameRepository->getAllEnabledMiniGames();
        \request()->type = 1;
        $miniGames = AllGameResource::collection($miniGames)->toArray(request());

        // Prepare data
        return [
            'full' => $fullGames,
            'mini' => $miniGames
        ];
    }

    public function updateGame($gameId, $user)
    {
        if (!$gameId) {
            return ['status' => 0, 'message' => 'please inter game_id', 'code' => 404];
        }

        $allGame = $this->allGameRepository->findGameById($gameId);

        if (!$allGame) {
            return ['status' => 0, 'message' => 'game not found', 'code' => 404];
        }

        $this->userRepository->updateUserGame($user, $gameId);

        return ['status' => 1, 'message' => 'updated', 'code' => 200];
    }
}
