<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Models\Room;
use App\Models\User;
use App\Models\GameWallet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Helpers\UserCoinLogHelper;
use App\Enums\UserCoinLogType;

class LeaderCCgameController extends Controller
{

    private function json($errorCode = 0, $message = 'success', $data = [])
    {
        return response()->json([
            'errorCode' => $errorCode,
            'errorMsg'  => $message,
            'data'      => $data
        ]);
    }

    private function safe(callable $fn)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::error("LeaderCC Error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return $this->json(500, 'Server error' . $e->getMessage());
        }
    }


    public function userInformation(Request $request)
    {
        return $this->safe(function () use ($request) {

            if (!$request->uid || !$request->gameId || !$request->token) {
                return $this->json(4005, 'Missing parameters');
            }

            if ($err = $this->checkWallet($request)) {
                return $err;
            }

            $user = User::with(['profile:id,user_id,avatar', 'UserVip:id,user_id,level'])
                ->select('id', 'name', 'di')
                ->find($request->uid);

            if (!$user) {
                return $this->json(4005, 'user not found');
            }

            return $this->json(0, 'success', [
                'uid'      => $user->id,
                'nickname' => $user->name,
                'avatar'   => getImagePath($user->profile->avatar),
                'coin'     => $user->di,
                'vipLevel' => $user->UserVip->level ?? 0,
                'water' => @$user->gamePercentage->percentageGame->percentage_game ?? 2.00,


            ]);
        });
    }


    public function updateGameCoin(Request $request)
    {
        return $this->safe(function () use ($request) {

            $required = ['orderId', 'gameId', 'roundId', 'uid', 'coin', 'type', 'rewardType', 'token', 'sign'];
            $missing = array_filter($required, fn($r) => !$request->filled($r) && $request->input($r) !== "0");
            if ($missing) return $this->json(4005, 'Invalid params');

            if ($err = $this->checkWallet($request)) {
                return $err;
            }

            $type = (int)$request->type;
            if (!in_array($type, [1, 2])) {
                return $this->json(4005, 'Invalid type');
            }


            return DB::transaction(function () use ($request, $type) {

                $user = User::lockForUpdate()->with([
                    'profile:id,user_id,avatar',
                    'nowGame:id,image',
                    'nowRoom:id,uid'
                ])->find($request->uid);

                if (!$user) return $this->json(4005, 'User not found');

                $coin = abs((int)$request->coin);

                if ($type == 1 && $user->di < $coin) {
                    return $this->json(4004, 'Insufficient game coins');
                }

                $amountBefore = $user->di;
                $user->di = $type == 1 ? ($user->di - $coin) : ($user->di + $coin);
                $user->save();
                $amount = abs($coin);
                $sign   = $type == 1 ? -1 : 1;
                UserCoinLogHelper::logByType(
                    $user->id,
                    $sign * $amount,
                    $amountBefore,
                    UserCoinLogType::COIN_GAME,
                    null,
                );

                DB::table('coin_game_users')->insert([
                    'user_id'          => $user->id,
                    'coins'            => $coin,
                    'app_profit_coins' => $coin,
                    'type'             => $type == 1 ? 0 : 1,
                    'game_id'          => $request->gameId,
                    'round_id'         => $request->roundId,
                    'order_id'         => $request->orderId,
                    'created_at'       => now(),
                    'updated_at'       => now()
                ]);

                Cache::put("order_{$request->orderId}", true, now()->addMinutes(30));

                dispatch(new \App\Jobs\GameWalletJop($type == 1 ? -$coin : $coin));

                $gameMapWinCoins = Common::getConfig('game_map_win_coins') ?? 10000;
                if ($type == 2 && $coin >= $gameMapWinCoins) {
                    $roomId = $user->nowRoom?->id;

                    $d = [
                        "messageContent" => [
                            "message" => "SBG",
                            "event" => "baishun.game.event",
                            'uImage'  => $user->profile?->avatar ?? 0,
                            'uName'   => $user->name ?? '',
                            'uId'     => $user->id ?? 0,
                            'coins'   => numToStringNew($coin),
                            //                            'coins'   => numToStringNew((int) $request->currency_diff),
                            "gImage"  => @$user->nowGame?->image
                        ]
                    ];

                    $json = json_encode($d);
                    dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $user->id,  $roomId, false), 'heavyProcessing');
                }

                return $this->json(0, 'success', [
                    'coins' => $user->di
                ]);
            });
        });
    }


    public function makeUpOrders(Request $request)
    {
        return $this->safe(function () use ($request) {

            $validator = Validator::make($request->all(), [
                'orderId'     => 'required',
                'gameId'      => 'required',
                'roundId'     => 'required',
                'uid'         => 'required',
                'coin'        => 'required|numeric',
                'rewardType'  => 'required|integer',
                'sign'        => 'required'
            ]);

            if ($validator->fails()) {
                return $this->json(4005, 'Missing or invalid parameters', $validator->errors());
            }

            if (Cache::has("order_{$request->orderId}")) {
                $user = User::find($request->uid);
                return $this->json(0, 'success', ['coin' => $user->di ?? 0]);
            }

            return DB::transaction(function () use ($request) {
                $user = User::lockForUpdate()->find($request->uid);
                if (!$user) return $this->json(4005, 'user not found');

                $coin = abs((int)$request->coin);
                $amountBefore = $user->di;
                $user->di += $coin;
                $user->save();
                $amount = abs($coin);
                $sign   =  1;
                UserCoinLogHelper::logByType(
                    $user->id,
                    $sign * $amount,
                    $amountBefore,
                    UserCoinLogType::COIN_GAME,
                    null,
                );
                DB::table('coin_game_users')->insert([
                    'user_id'    => $user->id,
                    'coins'      => $coin,
                    'app_profit_coins' => $coin,
                    'type'       => 1,
                    'game_id'    => $request->gameId,
                    'round_id'   => $request->roundId,
                    'order_id'   => $request->orderId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Cache::put("order_{$request->orderId}", true, now()->addHour());
                dispatch(new \App\Jobs\GameWalletJop($coin));

                return $this->json(0, 'success', ['coin' => $user->di]);
            });
        });
    }




    public function checkWallet($request)
    {
        if ($request->type == 1 && $this->checkLoseWallet($request->coin)) {
            return $this->json(4005, 'game not available');
        }
        return null;
    }

    public function checkLoseWallet(float $coins): bool
    {
        $wallet = GameWallet::filterByMonth()->first();
        if (!$wallet) return true;

        return ($wallet->used + $coins) >= $wallet->balance;
    }
}