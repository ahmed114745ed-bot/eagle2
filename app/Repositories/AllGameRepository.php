<?php

namespace App\Repositories;

use App\Models\AllGame;
use Illuminate\Database\Eloquent\Model;

class AllGameRepository
{
    public function getAllEnabledGames()
    {
        return AllGame::query()
            ->where(fn($q) => $q->where('url', '!=', null)->where('url', '!=', ''))
            ->where('is_enable', true)
            ->get();
    }

    public function getAllEnabledMiniGames()
    {
        return AllGame::query()
            ->where(fn($q) => $q->where('mini_url', '!=', null)->where('mini_url', '!=', ''))
            ->where('is_enable', true)
            ->get();
    }

    public function findGameById($gameId)
    {
        return AllGame::find($gameId);
    }

    public function all()
    {
        return AllGame::get();
    }

    public function create($data)
    {
        AllGame::create($data);
        return true;
    }

    public function update($id,$data)
    {
        AllGame::find($id)->update($data);
        return true;
    }

    public function updateSwitch($id,$data)
    {
        AllGame::find($id)->update($data);
        return true;
    }
}
