<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserCoinLogType;
use App\Helpers\Common;
use App\Helpers\UserCoinLogHelper;
use App\Http\Controllers\Controller;
use App\Models\GameSeat;
use App\Models\GameSession;
use App\Models\RewardWinnerGame;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;



class NewLeaderCCGameController extends Controller
{

    /**
     * APP ID for LeaderCC
     */
    private function appId(): string
    {
        return config('games.leader_CC_app_id', '');
    }

    /**
     * Generate a Sanctum token for the user to use in-game.
     * Token must remain valid for the entire game session.
     */
    private function generateToken(User $user): string
    {
        return $user->createToken('leader-cc-game')->plainTextToken;
    }



    /*
    |--------------------------------------------------------------------------
    | 1. Launch Game
    |--------------------------------------------------------------------------
    */
    public function urlGames(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roomId' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'msg' => $validator->errors()->first()]);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 0, 'msg' => 'Unauthenticated.'], 401);
        }

        $room = Room::find((int)$request->roomId);
        if (!$room) {
            return response()->json(['status' => 0, 'msg' => 'Room not found.'], 404);
        }

        $token = $this->generateToken($user);

        $url = "https://games.leadercc.com/test/index.html?" . http_build_query([
            'uid' => $user->id,
            'token' => $token,
            'lang' => app()->getLocale(),
            'roomid' => $request->roomId
        ]);

        return response()->json([
            'status' => 1,
            'gameUrl' => $url
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Verify Sign
    |--------------------------------------------------------------------------
    */
    private function generateSign(array $params)
    {
        return strtoupper(md5(implode('', $params) . $this->appId()));
    }

    private function verifySign(array $params, $sign)
    {
        return $this->generateSign($params) === $sign;
    }






    public function usersUpMic(Request $request)
    {
        if (!$request->roomId || !$request->uid || !$request->orderId || !$request->gameId) {
            return response()->json(['errorCode' => 5009]);
        }

        $room = Room::with('microphones')->find((int)$request->roomId);
        if (!$room) {
            return response()->json(['errorCode' => 4004]);
        }
        $usersUpMics = $room->microphones()->with('user')->where('status', 1)->get();

        return response()->json([
            'errorCode' => 0,
            'data' => [
                'list' =>
                $usersUpMics->map(function ($usersUpMic, $index) {
                    return [
                        "uid" => (string)$usersUpMic->user->id,
                        "location" => $usersUpMic->position, // أو seat لو عندك
                        "nickname" => $usersUpMic->user->name ?? '',
                        "avatar" => $usersUpMic->user->avatar ?? '',
                    ];
                })->values()


            ]
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | 3. Query User Info
    |--------------------------------------------------------------------------
    */
    public function userInfo(Request $request)
    {
        if (!$request->uid) {
            return response()->json(['errorCode' => 5009]);
        }

        $user = User::find((int)$request->uid);

        if (!$user) {
            return response()->json(['errorCode' => 4002]);
        }

        return response()->json([
            'errorCode' => 0,
            'data' => [
                'uid' => $user->id,
                'nickname' => $user->name,
                'avatar' => $user->avatar
            ]
        ]);
    }




    /*
    |--------------------------------------------------------------------------
    | 4. Sit Down
    |--------------------------------------------------------------------------
    */
    public function sitDown(Request $request)
    {

        if (!$request->uid || !$request->roomId || !$request->location || !$request->orderId || !$request->gameId || !$request->fees) {
            return response()->json(['errorCode' => 5009]);
        }


        $roomId = (int)$request->roomId;
        $uid = (int)$request->uid;
        $user = User::find($uid);
        $di = $user->di;

        if (!$user) {
            return response()->json(['errorCode' => 4002]);
        }

        // مثال: تحقق من الرصيد
        if ($user->di < $request->fees) {
            return response()->json(['errorCode' => 5204]);
        }

        $user->di -= $request->fees;
        $user->save();

        UserCoinLogHelper::logByType(
            $user->id,
            -abs($request->fees),
            $di,
            UserCoinLogType::COIN_GAME,
            null,
        );

        // تحقق إن الكرسي مش متاخد
        $exists = GameSeat::where('room_id', $roomId)
            ->where('location', $request->location)->where('orderId', $request->orderId)->where('game_id', $request->gameId)->whereNull('end_rank')->exists();

        if ($exists) {
            return response()->json(['errorCode' => 5007]);
        }

        GameSeat::create([
            'room_id' => $roomId,
            'user_id' => $user->id,
            'location' => $request->location,
            'orderId' => $request->orderId,
            'game_id' => $request->gameId,
            'fees' => $request->fees,
        ]);

        return response()->json([
            'errorCode' => 0,
            'data' => 'success'
        ]);
    }


    public function standUp(Request $request)
    {
        // 1. Verify sign
        if (!$this->verifySign([
            $request->orderId,
            $request->gameId,
            $request->roomId,
            $request->uid,
            $request->location
        ], $request->sign)) {
            return response()->json(['errorCode' => 5009]);
        }


        $roomId = (int)$request->roomId;
        $uid = (int)$request->uid;
        // 3. Remove user from seat
        GameSeat::where('room_id', $roomId)
            ->where('user_id', $uid)->where('orderId', $request->orderId)->where('game_id', $request->gameId)
            ->where('location', $request->location)
            ->update(['location' => null]);

        return response()->json([
            'errorCode' => 0,
            'data' => 'success'
        ]);
    }


    public function gameStart(Request $request)
    {
        if (!$this->verifySign([
            $request->orderId,
            $request->gameId,
            $request->roomId,
            $request->uid
        ], $request->sign)) {
            return response()->json(['errorCode' => 5009]);
        }
        Room::where('id', (int)$request->roomId)
            ->update(['game_id' => (int)$request->gameId]);

        GameSession::create([
            'game_id' => $request->gameId,
            'room_id' => $request->roomId,
            'owner_uid' => $request->uid,
            'started_at' => now(),
            'player_list' => $request->playerList,
        ]);
        // يمكنك تنفيذ أي منطق تريده عند بدء اللعبة هنا

        return response()->json([
            'errorCode' => 0,
            'data' => 'success'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Game End
    |--------------------------------------------------------------------------
    */
    public function gameEnd(Request $request)
    {
        if (!$this->verifySign([
            $request->orderId,
            $request->gameId,
            $request->roomId,
            $request->uid
        ], $request->sign)) {
            return response()->json(['errorCode' => 5009]);
        }
        $roomId = (int)$request->roomId;
        foreach ($request->rankList as $index => $uid) {
            $user = User::find((int)$uid);
            $di = $user->di;
            if (!$user) continue;

            $reward = RewardWinnerGame::where('rank', $index + 1)->first();
            if (!$reward) continue;

            $user->di += $reward->coins;
            $user->save();

            UserCoinLogHelper::logByType(
                $user->id,
                $reward->coins,
                $di,
                UserCoinLogType::COIN_GAME,
                null,
            );

            GameSeat::where('room_id', $roomId)
                ->where('user_id', $uid)->where('orderId', $request->orderId)->where('game_id', $request->gameId)
                ->where('location', $request->location)->whereNull('end_rank')->update(
                    [
                        'end_rank' => $index + 1,
                        'coin_reward' => $reward->coins
                    ]
                );
        }


        $gameSession =  GameSession::where([
            'game_id' => $request->gameId,
            'room_id' => $request->roomId,
            'owner_uid' => $request->uid,

        ])->whereNull('ended_at')->first();
        $gameSession->update([

            'ended_at' => now(),
            'reason' => $request->reason,
            'rankList' => $request->rankList,
            'room_destroy' => $request->room_destroy
        ]);
        return response()->json([
            'errorCode' => 0,
            'data' => 'success'
        ]);
    }
}
