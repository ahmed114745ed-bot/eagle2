<?php

namespace App\Services;

use App\Helpers\Common;
use App\Http\Resources\AllGameResource;
use App\Repositories\AllGameRepository;
use App\Repositories\User\UserRepository;

class AllGameService
{
    protected $allGameRepository;
    protected $userRepository;

    public function __construct(AllGameRepository $allGameRepository, UserRepository $userRepository)
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

        $key = [
            "baishun_channel" => config('app.baishun_channel') ?? '',
            "baishun_app_id" => config('app.baishun_app_id') ?? '',
        ];
        return [
            'full' => $fullGames,
            'mini' => $miniGames,
            'credentials'  => $key,
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

    public function utdIndex()
    {
        return $this->allGameRepository->all();
    }

    public function createUtd($request)
    {
        $image = null;
        if ($request->hasFile('image')) {
            $image = Common::upload('images', $request->file('image'));
        }
        $data = [
            'name' => $request->name,
            'name_en' => $request->name_en,
            'url' => $request->url,
            'image' => $image ?? '',
            'mini_url' => $request->mini_url,
            'type' => $request->type,
            'is_enable' => $request->is_enable,
            'custom_id' => $request->custom_id,
        ];
        return $this->allGameRepository->create($data);
    }

    public function updateUtd($request)
    {

        if ($request->hasFile('image')) {
            $image = Common::upload('images', $request->file('image'));
            $data = [
                'name' => $request->name,
                'name_en' => $request->name_en,
                'url' => $request->url,
                'image' => $image,
                'mini_url' => $request->mini_url,
                'type' => $request->type,
                'is_enable' => $request->is_enable,
                'custom_id' => $request->custom_id,
            ];
        } else {
            $data = [
                'name' => $request->name,
                'name_en' => $request->name_en,
                'url' => $request->url,
                'mini_url' => $request->mini_url,
                'type' => $request->type,
                'is_enable' => $request->is_enable,
                'custom_id' => $request->custom_id,
            ];
        }

        return $this->allGameRepository->update($request->game_id, $data);
    }

    public function updateSwitch($request)
    {
        $data = [
            'is_enable' => $request->is_enable,
        ];
        return $this->allGameRepository->updateSwitch($request->game_id, $data);
    }

    public function show($game_id)
    {
        return $this->allGameRepository->findById($game_id);
    }
}
