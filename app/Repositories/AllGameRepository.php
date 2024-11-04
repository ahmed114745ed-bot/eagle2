<?php

namespace App\Repositories;

use App\Models\AllGame;
use Illuminate\Support\Facades\DB;
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
        return AllGame::orderBy('id')->get();
    }

    public function gameByTotalGain()
    {
        $games = AllGame::with(['coinGameUser' => function ($query) {
            $query->whereMonth('created_at', date('m'))->select('game_id', DB::raw("
                SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) AS total_gain
            "))->groupBy('game_id');
        }])->get()->sortByDesc(function ($game) {
            return $game->coinGameUser->first()->total_gain ?? 0;
        });
        return  array_values($games->toArray());
    }

    public function create($data)
    {
        AllGame::create($data);
        return true;
    }

    public function update($id, $data)
    {
        AllGame::find($id)->update($data);
        return true;
    }

    public function updateSwitch($id, $data)
    {
        AllGame::find($id)->update($data);
        return true;
    }

    public function findById($game_id)
    {
        return AllGame::find($game_id);
    }
}
