<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tik\Services\AdminUsersService;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V1\AdminUsersResource;
use App\Http\Resources\Api\V1\GameReportResource;
use App\Models\CoinGameUser;
use App\Models\User;

class GameReportController extends Controller
{
    public function allPlayers()
    {
        $startDate = request('start_date') ?? date("Y-m-d"); 
        $endDate = request('end_date') ?? date("Y-m-d"); 
        
        $players = CoinGameUser::selectRaw('
                MAX(coin_game_users.created_at) as earliest_created_at, 
                coin_game_users.user_id, 
                MAX(users.name) as user_name, 
                SUM(CASE WHEN coin_game_users.type = 1 THEN coin_game_users.coins ELSE 0 END) as total_coins_win, 
                SUM(CASE WHEN coin_game_users.type = 0 THEN coin_game_users.coins ELSE 0 END) as total_coins_lose
            ')
            ->leftJoin('users', 'coin_game_users.user_id', '=', 'users.id')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('coin_game_users.created_at', [$startDate, $endDate]);
            })
            ->groupBy('coin_game_users.user_id')
            ->orderByDesc('earliest_created_at')
            ->get()
            ->map(function ($player) {
                // Calculate total_app_gain for each player
                $player->total_app_gain = $player->total_coins_lose - $player->total_coins_win;
                return $player;
            });

        return Common::apiResponse(1, '', $players);
    }

    public function playerDetails($id)
    {
        $userId = $id;
        $gameId = request("game_id");
        $startDate = request('start_date'); 
        $endDate = request('end_date'); 

        $data = CoinGameUser::with(["game:id,name"])
        ->selectRaw('
            coin_game_users.game_id,
            SUM(CASE WHEN coin_game_users.type = 1 THEN coin_game_users.coins ELSE 0 END) as total_coins_win, 
            SUM(CASE WHEN coin_game_users.type = 0 THEN coin_game_users.coins ELSE 0 END) as total_coins_lose
        ')
        ->leftJoin('users', 'coin_game_users.user_id', '=', 'users.id')
        ->where('users.id', $userId)
        ->when($gameId, function ($q) use ($gameId) {
            $q->where('coin_game_users.game_id', $gameId);
        })
        ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->whereBetween('coin_game_users.created_at', [$startDate, $endDate]);
        })
        ->groupBy('coin_game_users.game_id')
        ->get();
    
        return Common::apiResponse(1, '', $data);
    }

    public function gameRanking($id, Request $request)
    {
        $gameId = $id;
        $type = $request->input('type');

        $data = CoinGameUser::with(['game:id,name', 'user:id,name']) 
            ->selectRaw('
                coin_game_users.user_id,
                SUM(CASE WHEN coin_game_users.type = 1 THEN coin_game_users.coins ELSE 0 END) as total_coins_win, 
                SUM(CASE WHEN coin_game_users.type = 0 THEN coin_game_users.coins ELSE 0 END) as total_coins_lose
            ')
            ->leftJoin('users', 'coin_game_users.user_id', '=', 'users.id')
            ->when($gameId, function ($q) use ($gameId) {
                $q->where('coin_game_users.game_id', $gameId);
            })
            ->groupBy('coin_game_users.user_id')
            ->orderByRaw($type == 1 ? 'total_coins_win DESC' : 'total_coins_lose DESC')
            ->limit(10) 
            ->get();

        return Common::apiResponse(1, '', $data);
    }

}
