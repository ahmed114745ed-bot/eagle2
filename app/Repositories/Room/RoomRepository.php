<?php
namespace App\Repositories\Room;

use App\Models\Room;

class RoomRepository 
{

    public $model;
    public function __construct (Room $model)
    {
        $this->model = $model; 
    }

    public function getRoomsByGameId($gameId = null)
    {
        return $this->model->where('game_id', '!=', null)
                    ->where('mode', 4)
                    ->when(isset($gameId) && $gameId != 'null', function ($query) use ($gameId) {
                        $query->where('game_id', $gameId);
                    })
                    ->get();
    }
}
