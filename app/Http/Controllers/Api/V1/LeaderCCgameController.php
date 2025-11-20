<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\GameWallet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class LeaderCCgameController extends Controller
{


    public function userInformation(Request $request)
    {
        if (!$request->uid || !$request->gameId || !$request->token) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg' => 'Missing parameters'
            ], 200);
        }
       $errorExists = $this->checkWallet($request);
       if ($errorExists ) return response()->json($errorExists);

       /* $userId = $this->findUserByToken($request->token);
       if($userId != $request->uid){
            return response()->json([
                            'errorCode' => 4005,
                            'errorMsg'  => 'user not found',
                        ], 200);
       } */
        $user = User::with(['profile:id,user_id,avatar', 'UserVip:id,user_id,level'])
                ->select('id', 'name', 'di')
                ->find($request->uid);
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
        // Fast custom validation
        if (!$request->orderId || !$request->gameId || !$request->roundId ||
            !$request->uid || !isset($request->coin) || !$request->type ||
            !isset($request->rewardType) || !$request->token || !$request->sign) {
            return response()->json([
                'errorCode' => 4005,
                'message'   => 'Invalid params'
            ], 200);
        }

        $errorExists = $this->checkWallet($request);
        if ($errorExists) return response()->json($errorExists);


        // Validate type is 1 or 2
        $type = (int)$request->type;
        if (!in_array($type, [1, 2])) {
            return response()->json([
                'errorCode' => 4005,
                'message'   => 'Invalid type'
            ], 200);
        }

        $orderId = $request->orderId;
        $coin = (int)$request->coin;
        $uid = $request->uid;

        // Check cache for duplicate order
        if (Cache::has("order_{$orderId}")) {
            return response()->json([
                'errorCode' => 10003,
                'message'   => 'Order already exists'
            ], 200);
        }

        DB::beginTransaction();

        try {
            // Lock and get user
            $user = User::where('id', $uid)->lockForUpdate()->first();

            if (!$user) {
                DB::rollBack();
                return response()->json([
                    'errorCode' => 4005,
                    'message'   => 'User not found'
                ], 200);
            }

            // Check sufficient balance for deduction
            if ($type == 1 && $user->di < $coin) {
                DB::rollBack();
                return response()->json([
                    'errorCode' => 4004,
                    'message'   => 'Insufficient game coins'
                ], 200);
            }

            // Update user balance
            if ($type == 1) {
                $user->di -= $coin;
            } else {
                $user->di += $coin;
            }

            $user->save();

            // Log transaction
            DB::table('coin_game_users')->insert([
                'user_id' => $user->id,
                'coins' => abs($coin),
                'app_profit_coins' => abs($coin),
                'type' => $type == 1 ? 0 : 1,
                'game_id' => $request->gameId,
                'round_id' => $request->roundId,
                'order_id' => $orderId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Cache the order to prevent duplicates
            Cache::put("order_{$orderId}", true, now()->addMinutes(30));

            dispatch(new \App\Jobs\GameWalletJop($request->coins * (($type == 1) ? -1 : 1) ));


            DB::commit();

            return response()->json([
                'errorCode' => 0,
                'data' => [
                    'coins' => $user->di
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateGameCoin error: ' . $e->getMessage());

            return response()->json([
                'errorCode' => 500,
                'message' => 'Server error'
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

    public function findUserByToken($token): mixed
    {
        $personalToken = @PersonalAccessToken::findToken($token);
        if (!$personalToken) {
            return null;
        }

        $userId = $personalToken->tokenable_id;

        // Get only the token column instead of full user model
        $lastToken = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $userId)
            ->orderByDesc('id')
            ->value('token');

        if (!$lastToken) {
            return null;
        }

        // Extract token from pipe format if needed
        if (strpos($token, '|') !== false) {
            [$_, $token] = explode('|', $token, 2);
        }

        $hashedToken = hash('sha256', $token);

        if (!hash_equals($lastToken, $hashedToken)) {
            return null;
        }

        return $userId;
    }

    public function checkWallet($request)
    {
        if ($request->type == 1 && $this->checkLoseWallet($request->coins)) {
            $responseArray = [
                'errorCode' => 4005,
                'errorMsg' => 'game not available'
            ];
            return response()->json($responseArray);
        }
        return null;
    }

    public function checkLoseWallet(float $coins) : bool
    {
        $gameWallet = GameWallet::filterByMonth()->first();
        $gameUsed = $gameWallet?->used ?? 0;
        $used = $gameUsed + $coins;

        return !$gameWallet || $used >= $gameWallet->balance;
    }
}
