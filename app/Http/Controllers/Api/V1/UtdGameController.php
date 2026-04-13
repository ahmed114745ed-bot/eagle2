<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Models\User;
use App\Models\GameWallet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class UtdGameController extends Controller
{
    private function json($errorCode = 0, $message = 'success', $data = [])
    {
        return response()->json([
            'errorCode' => $errorCode,
            'errorMsg'  => $message,
            'data'      => $data,
        ]);
    }

    private function safe(callable $fn)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            Log::error('UTD Game Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->json(500, 'Server error');
        }
    }

    /**
     * POST /api/utd-game/get-user-info
     * UTD calls this to fetch user info before starting a game session.
     */
    public function getUserInfo(Request $request)
    {
        return $this->safe(function () use ($request) {
            if (!$request->uid) {
                return $this->json(4005, 'Missing uid');
            }

            $user = User::with(['profile:id,user_id,avatar', 'UserVip:id,user_id,level'])
                ->select('id', 'name', 'di')
                ->find($request->uid);

            if (!$user) {
                return $this->json(4005, 'User not found');
            }

            return $this->json(0, 'success', [
                'uid'      => $user->id,
                'nickname' => $user->name,
                'avatar'   => getImagePath($user->profile->avatar),
                'coin'     => $user->di,
                'vipLevel' => $user->UserVip->level ?? 0,
            ]);
        });
    }

    /**
     * POST /api/utd-game/change-balance
     * UTD calls this to deduct or add coins after a game round.
     * type: 1 = deduct (lose), 2 = add (win)
     */
    public function changeBalance(Request $request)
    {
        return $this->safe(function () use ($request) {
            $validator = Validator::make($request->all(), [
                'orderId'    => 'required|string',
                'gameId'     => 'required|string',
                'roundId'    => 'required|string',
                'uid'        => 'required|integer',
                'coin'       => 'required|numeric',
                'type'       => 'required|in:1,2',
            ]);

            if ($validator->fails()) {
                return $this->json(4005, 'Invalid params', $validator->errors());
            }

            $type = (int) $request->type;

            // Idempotency check — prevent duplicate orders from retry after network timeout
            $existingOrder = DB::table('coin_game_users')
                ->where('order_id', $request->orderId)
                ->first();

            if ($existingOrder) {
                Log::info("UTD changeBalance: Order already processed", [
                    'orderId' => $request->orderId,
                    'userId' => $request->uid
                ]);
                $user = User::find($request->uid);
                return $this->json(0, 'success', ['coin' => $user->di ?? 0]);
            }

            if ($type == 1 && $this->checkLoseWallet($request->coin)) {
                return $this->json(4005, 'Game not available');
            }

            return DB::transaction(function () use ($request, $type) {
                $user = User::lockForUpdate()->with([
                    'profile:id,user_id,avatar',
                    'nowGame:id,image',
                    'nowRoom:id,uid',
                ])->find($request->uid);

                if (!$user) {
                    return $this->json(4005, 'User not found');
                }

                $coin = abs((int) $request->coin);

                if ($type == 1 && $user->di < $coin) {
                    return $this->json(4004, 'Insufficient game coins');
                }

                $user->di = $type == 1 ? ($user->di - $coin) : ($user->di + $coin);
                $user->save();

                DB::table('coin_game_users')->insert([
                    'user_id'          => $user->id,
                    'coins'            => $coin,
                    'app_profit_coins' => $coin,
                    'type'             => $type == 1 ? 0 : 1,
                    'game_id'          => $request->gameId,
                    'round_id'         => $request->roundId,
                    'order_id'         => $request->orderId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                Cache::put("utd_order_{$request->orderId}", true, now()->addMinutes(30));

                dispatch(new \App\Jobs\GameWalletJop($type == 1 ? -$coin : $coin));

                // Broadcast big wins
                $gameMapWinCoins = Common::getConfig('game_map_win_coins') ?? 10000;
                if ($type == 2 && $coin >= $gameMapWinCoins) {
                    $roomId = $user->nowRoom?->id;
                    $d = [
                        'messageContent' => [
                            'message' => 'SBG',
                            'event'   => 'baishun.game.event',
                            'uImage'  => $user->profile?->avatar ?? 0,
                            'uName'   => $user->name ?? '',
                            'uId'     => $user->id ?? 0,
                            'coins'   => numToStringNew($coin),
                            'gImage'  => $user->nowGame?->image,
                        ],
                    ];
                    $json = json_encode($d);
                    dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $user->id, $roomId, false), 'heavyProcessing');
                }

                return $this->json(0, 'success', ['coin' => $user->di]);
            });
        });
    }

    /**
     * POST /api/utd-game/make-up-orders
     * UTD calls this to retry a failed order (always adds coins).
     */
    public function makeUpOrders(Request $request)
    {
        return $this->safe(function () use ($request) {
            $validator = Validator::make($request->all(), [
                'orderId'  => 'required|string',
                'gameId'   => 'required|string',
                'roundId'  => 'required|string',
                'uid'      => 'required|integer',
                'coin'     => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return $this->json(4005, 'Invalid params', $validator->errors());
            }

            // Idempotency — check database first, then cache
            $existingOrder = DB::table('coin_game_users')
                ->where('order_id', $request->orderId)
                ->first();

            if ($existingOrder) {
                Log::info("UTD makeUpOrders: Order already exists in database", [
                    'orderId' => $request->orderId,
                    'userId' => $request->uid
                ]);
                $user = User::find($request->uid);
                return $this->json(0, 'success', ['coin' => $user->di ?? 0]);
            }

            if (Cache::has("utd_order_{$request->orderId}")) {
                $user = User::find($request->uid);
                return $this->json(0, 'success', ['coin' => $user->di ?? 0]);
            }

            return DB::transaction(function () use ($request) {
                $user = User::lockForUpdate()->find($request->uid);
                if (!$user) {
                    return $this->json(4005, 'User not found');
                }

                $coin = abs((int) $request->coin);
                $user->di += $coin;
                $user->save();

                DB::table('coin_game_users')->insert([
                    'user_id'          => $user->id,
                    'coins'            => $coin,
                    'app_profit_coins' => $coin,
                    'type'             => 1,
                    'game_id'          => $request->gameId,
                    'round_id'         => $request->roundId,
                    'order_id'         => $request->orderId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                Cache::put("utd_order_{$request->orderId}", true, now()->addHour());
                dispatch(new \App\Jobs\GameWalletJop($coin));

                return $this->json(0, 'success', ['coin' => $user->di]);
            });
        });
    }

    private function checkLoseWallet(float $coins): bool
    {
        $wallet = GameWallet::filterByMonth()->first();
        if (!$wallet) return true;

        return ($wallet->used + $coins) >= $wallet->balance;
    }
}
