<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RoomHelper
{
    public function checkUserIsAdminOrOwner(string $admins, $ownerId): bool
    {
        $userId = Auth::id();
        $admins = explode (',',$admins);
        return ($userId == $ownerId || in_array ($userId, $admins) );
    }

    public function gameWhoWin($answer_player_one,$answer_player_two,$record_game){
        if ($answer_player_one == 0 && $answer_player_two == 0) {
            $record_game->type = "equal";
            $record_game->save();
        }elseif ($answer_player_one == 0 && $answer_player_two == 1) {
            $record_game->type = "win";
            $record_game->player_win_id = $record_game->player_two_id;
            $record_game->save();
        }elseif ($answer_player_one == 0 && $answer_player_two == 2) {
            $record_game->type = "win";
            $record_game->player_win_id = $record_game->player_one_id;
            $record_game->save();
        }elseif ($answer_player_one == 1 && $answer_player_two == 0) {
            $record_game->type = "win";
            $record_game->player_win_id = $record_game->player_one_id;
            $record_game->save();
        }elseif ($answer_player_one == 1 && $answer_player_two == 1) {
            $record_game->type = "equal";
            $record_game->save();
        }elseif ($answer_player_one == 1 && $answer_player_two == 2) {
            $record_game->type = "win";
            $record_game->player_win_id = $record_game->player_two_id;
            $record_game->save();
        }elseif ($answer_player_one == 2 && $answer_player_two == 0) {
            $record_game->type = "win";
            $record_game->player_win_id = $record_game->player_two_id;
            $record_game->save();
        }elseif ($answer_player_one == 2 && $answer_player_two == 1) {
            $record_game->type = "win";
            $record_game->player_win_id = $record_game->player_one_id;
            $record_game->save();
        }elseif ($answer_player_one == 2 && $answer_player_two == 2) {
            $record_game->type = "equal";
            $record_game->save();
        }
    }

    public function gameWhoWinInZahr($answer_player_one,$answer_player_two,$record_game){
        if ($answer_player_one == $answer_player_two) {
            $record_game->type = "equal";
            $record_game->save();
        }elseif ($answer_player_one > $answer_player_two) {
            $record_game->type = "win";
            $record_game->player_win_id = $record_game->player_one_id;
            $record_game->save();
        }elseif ($answer_player_two > $answer_player_one) {
            $record_game->type = "win";
            $record_game->player_win_id = $record_game->player_two_id;
            $record_game->save();
        }
    }

}
