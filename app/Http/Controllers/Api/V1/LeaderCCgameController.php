<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class LeaderCCgameController extends Controller
{


    public function userInformation(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'gameId' => 'required|string',
            'uid'    => 'required|string',
            'token'  => 'required|string',
            'roomId' => 'required|string',
            'sign'   => 'required|string',


        ]);

        if ($validator->fails()) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing or invalid parameters',
                'errors'    => $validator->errors(),
            ], 4005);
        }
        $key = config('games.leader_CC_game_key');

        $expectedSign = md5(
            $request->gameId .
                $request->uid .
                $request->token .
                $request->roomId .
                $key
        );

        // 4️⃣ Compare provided sign
        if (strtolower($expectedSign) !== strtolower($request->sign)) {
            return response()->json([
                'errorCode' => 10004,
                'errorMsg'  => 'Verify signature fail',
            ], 10004);
        }
        $user = User::find($request->uid);
        if (!$user) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'user not found',
            ], 4005);
        }

        $userData = [
            'uid'       => $user->id,
            'nickname'  => $user->name,
            'avatar'    => getImagePath($user->profile->avatar),
            'coin'      => $user->di,
            'vipLevel'  => @$user->UserVip->level ?? 0,
        ];
        return response()->json([
            'errorCode' => 0,
            'data'      => $userData,
        ]);
    }

    public function updateGameCoin(Request $request)
    {
        // 1️⃣ Validate input
        $validator = Validator::make($request->all(), [
            'orderId'     => 'required|string',
            'gameId'      => 'required|string',
            'roundId'     => 'required|string',
            'uid'         => 'required|string',
            'coin'        => 'required|numeric',
            'type'        => 'required|in:1,2', // 1=consume, 2=obtain
            'rewardType'  => 'required|integer',
            'token'       => 'required|string',
            'winId'       => 'nullable|string',
            'roomid'      => 'nullable|string',
            'sign'        => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing or invalid parameters',
                'errors'    => $validator->errors(),
            ], 400);
        }

        // 2️⃣ Secret key from .env
        $key = config('games.leader_CC_game_key'); // put your real secret key in .env

        // 3️⃣ Prepare values for signature verification
        $orderId    = $request->orderId;
        $gameId     = $request->gameId;
        $roundId    = $request->roundId;
        $uid        = $request->uid;
        $coin       = $request->coin;
        $type       = $request->type;
        $rewardType = $request->rewardType;
        $token      = $request->token;
        $winId      = $request->winId ?? '';

        // 4️⃣ Generate expected sign
        $expectedSign = md5($orderId . $gameId . $roundId . $uid . $coin . $type . $rewardType . $token . $winId . $key);

        // 5️⃣ Compare signs
        if (strtolower($expectedSign) !== strtolower($request->sign)) {
            return response()->json([
                'errorCode' => 10004,
                'errorMsg'  => 'Verify signature fail',
            ], 10004);
        }
        Cache::put("order_$orderId", true, now()->addHour());
        $user = User::find($uid);
        if (!$user) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'user not found',
            ], 4005);
        }
        if ($type == 1) {
            $user->di -= $coin;
        } else {
            $user->di +=  $coin;
        }

        $user->save();

        DB::table('coin_game_users')->insert([
            'user_id' => $user->id,
            'coins' => abs($coin),
            'app_profit_coins' => abs($coin),
            'type' => $type,
            'game_id' => $gameId,
            'round_id' => $roundId,
            'order_id' => $orderId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 7️⃣ Return success response
        return response()->json([
            'errorCode' => 0,
            'data' => [
                'coin' => $user->di,
            ],
        ]);
    }
    public function validationOrderId($orderId)
    {
            if (Cache::has("order_$orderId")) {
                return [
                    'valid' => false,
                    'response' => response()->json([
                        'errorCode' => 10003,
                        'message' => 'Order already exists'
                    ]),
                ];
            }
        }
    
}
