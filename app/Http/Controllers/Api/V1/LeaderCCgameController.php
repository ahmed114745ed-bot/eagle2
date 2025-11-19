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

        \Log::info(' userInformation ', [
            'request' => $request->all(),
        ]);
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
            ], 400);
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
            ], 400);
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
        \Log::info('updateGameCoin', ['request' => $request->all()]);

        $operations =  [$request->all()];

        DB::beginTransaction();

        try {
            $results = [];

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
                    $results[] = [
                        'errorCode' => 4005,
                        'errorMsg'  => 'Invalid params',
                        'errors'    => $validator->errors(),
                    ];
                    continue;
                }

                $op = $validator->validated();

                if (Cache::has("order_{$op['orderId']}")) {
                    $results[] = [
                        'uid' => $op['uid'],
                        'orderId' => $op['orderId'],
                        'errorCode' => 10003,
                        'errorMsg' => 'Repeat order',
                    ];
                    continue;
                }

                Cache::put("order_{$op['orderId']}", true, now()->addMinutes(30));

                $lock = Cache::lock("user_lock_{$op['uid']}", 5);

                if (!$lock->get()) {
                    $results[] = [
                        'uid' => $op['uid'],
                        'errorCode' => 5001,
                        'errorMsg' => 'User is currently busy, try later',
                    ];
                    continue;
                }

                try {
                    $user = User::where('id', $op['uid'])->lockForUpdate()->first();

                    if (!$user) {
                        $results[] = [
                            'uid' => $op['uid'],
                            'errorCode' => 4005,
                            'errorMsg' => 'User not found',
                        ];
                        continue;
                    }

                    $coin = (int)$op['coin'];
                    $type = (int)$op['type'];

                    if (DB::table('coin_game_users')->where('order_id', $op['orderId'])->exists()) {
                        $results[] = [
                            'uid' => $user->id,
                            'orderId' => $op['orderId'],
                            'errorCode' => 10003,
                            'errorMsg' => 'Repeat order (DB)',
                        ];
                        continue;
                    }

                    if ($type == 1 && $user->di < $coin) {
                        $results[] = [
                            'uid' => $user->id,
                            'orderId' => $op['orderId'],
                            'errorCode' => 4004,
                            'errorMsg' => 'Insufficient game coins',
                        ];
                        continue;
                    }

                    $type == 1 ? $user->di -= $coin : $user->di += $coin;
                    $logType =  $type == 1 ? 0 : 1 ;
                    $user->save();

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

                    $results[] = [
                        'uid' => $user->id,
                        'orderId' => $op['orderId'],
                        'errorCode' => 0,
                        'coin' => $user->di,
                    ];

                } finally {
                    $lock->release();
                }
            }

            DB::commit();

            return response()->json([
                'errorCode' => 0,
                'data' => $results
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'errorCode' => 500,
                'errorMsg' => 'Server error',
                'exception' => $e->getMessage(),
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
            ], 400);
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
                ], 400);
            }
    }
}
