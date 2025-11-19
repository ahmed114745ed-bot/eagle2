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
            ], 200);
        }
        $key = config('games.leader_CC_game_key');

        // $expectedSign = md5(
        //     $request->gameId .
        //         $request->uid .
        //         $request->token .
        //         $request->roomId .
        //         $key
        // );

        // // 4️⃣ Compare provided sign
        // if (strtolower($expectedSign) !== strtolower($request->sign)) {
        //     return response()->json([
        //         'errorCode' => 10004,
        //         'errorMsg'  => 'Verify signature fail',
        //     ], 400);
        // }
        $user = User::find($request->uid);
        if (!$user) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'user not found',
            ], 200);
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
        Log::info($request->all());
        $operations = [$request->all()];
    
        DB::beginTransaction();
    
        try {
            foreach ($operations as $op) {
    
                $validator = Validator::make($op, [
                    'orderId'     => 'required|string',
                    'gameId'      => 'required|string',
                    'roundId'     => 'required|string',
                    'uid'         => 'required|string',
                    'coin'        => 'required|numeric',
                    'type'        => 'required|in:1,2',
                    'rewardType'  => 'required|integer',
                    'token'       => 'required|string',
                    'winId'       => 'nullable|string',
                    'roomid'      => 'nullable|string',
                    'sign'        => 'required|string',
                ]);
    
                if ($validator->fails()) {
                    return response()->json([
                        'errorCode' => 4005,
                        'message'   => 'Invalid params'
                    ], 200);
                }
    
                $op = $validator->validated();
        Log::info('after condition');
    
                // if (Cache::has("order_{$op['orderId']}")) {
                //     return response()->json([
                //         'errorCode' => 10003,
                //         'message'   => 'Order already exists'
                //     ], 200);
                // }
    
                Cache::put("order_{$op['orderId']}", true, now()->addMinutes(30));
    
         
                
                $user = null;
                
                    $user = User::where('id', $op['uid'])->lockForUpdate()->first();
                
                    if (!$user) {
                        return response()->json([
                            'errorCode' => 4005,
                            'message'   => 'User not found'
                        ], 200);
                    }
                
                    $coin = (int)$op['coin'];
                    $type = (int)$op['type'];
                
                    // if (DB::table('coin_game_users')->where('order_id', $op['orderId'])->exists()) {
                    //     return response()->json([
                    //         'errorCode' => 10003,
                    //         'message'   => 'Order already exists'
                    //     ], 200);
                    // }
        Log::info('after type');
                
                    if ($type == 1 && $user->di < $coin) {
                        return response()->json([
                            'errorCode' => 4004,
                            'message'   => 'Insufficient game coins'
                        ], 200);
                    }
                
                    $type == 1 ? $user->di -= $coin : $user->di += $coin;
                    $logType = $type == 1 ? 0 : 1;
                    $user->save();
        Log::info('before coin_game_users');
                
                    DB::table('coin_game_users')->insert([
                        'user_id' => $user->id,
                        'coins' => abs($coin),
                        'app_profit_coins' => abs($coin),
                        'type' => $logType,
                        'game_id' => $op['gameId'],
                        'round_id' => $op['roundId'],
                        'order_id' => $op['orderId'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
        Log::info('after coin_game_users');
                
                
                
            }
    
            DB::commit();
    
            return response()->json([
                'errorCode' => 0,
                'data'   => [
                    'coins' => $user->di
                ]
            ]);
    
        } catch (\Exception $e) {
    
            DB::rollBack();
        Log::info($e->getMessage());
            
            return response()->json([
                'errorCode' => 500,
                'message'   => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function makeUpOrders(Request $request)
    {
        \Log::info(' makeUpOrders ', [
            'request' => $request->all(),
        ]);
        // 1️⃣ Validate input
        $validator = Validator::make($request->all(), [
            'orderId'     => 'required|string',
            'gameId'      => 'required|string',
            'roundId'     => 'required|string',
            'uid'         => 'required|string',
            'coin'        => 'required|numeric',
            'rewardType'  => 'required|integer',
            'winId'       => 'nullable|string',
            'roomid'      => 'nullable|string',
            'sign'        => 'required|string',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing or invalid parameters',
                'errors'    => $validator->errors(),
            ], 200);
        }

        // 2️⃣ Secret key from .env
        $key = config('games.leader_CC_game_key'); // put your real secret key in .env

        // 3️⃣ Prepare values for signature verification
        $orderId    = $request->orderId;
        $gameId     = $request->gameId;
        $roundId    = $request->roundId;
        $uid        = $request->uid;
        $coin       = $request->coin;
        $rewardType = $request->rewardType;
        $winId      = $request->winId ?? '';

        // 4️⃣ Generate expected sign
        // $expectedSign = md5($orderId . $gameId . $roundId . $uid . $coin . $rewardType . $winId . $key);

        // // 5️⃣ Compare signs
        // if (strtolower($expectedSign) !== strtolower($request->sign)) {
        //     return response()->json([
        //         'errorCode' => 10004,
        //         'errorMsg'  => 'Verify signature fail',
        //     ], 400);
        // }
        Cache::put("order_$orderId", true, now()->addHour());
        $user = User::find($uid);
        if (!$user) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'user not found',
            ], 200);
        }

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
            return response()->json([
                'errorCode' => 10003,
                'errorMsg'  => 'Order already exists'
            ], 200);
        }
        
        return response()->json([
            'errorCode' => 0,
            'errorMsg'  => 'Order is valid'
        ]);
    }
}