<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\GameWallet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

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

            return $this->json(500, 'Server error');
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
                'vipLevel' => $user->UserVip->level ?? 0
            ]);
        });
    }


    public function updateGameCoin(Request $request)
    {
        return $this->safe(function () use ($request) {

            $required = ['orderId','gameId','roundId','uid','coin','type','rewardType','token','sign'];
            foreach ($required as $r) {
                if (!$request->$r && $request->$r !== "0") {
                    return $this->json(4005, 'Invalid params');
                }
            }

            if ($err = $this->checkWallet($request)) {
                return $err;
            }

            $type = (int)$request->type;
            if (!in_array($type, [1, 2])) {
                return $this->json(4005, 'Invalid type');
            }

            if (Cache::has("order_{$request->orderId}")) {
                return $this->json(10003, 'Order already exists');
            }

            return DB::transaction(function () use ($request, $type) {

                $user = User::lockForUpdate()->find($request->uid);
                if (!$user) return $this->json(4005, 'User not found');

                $coin = abs((int)$request->coin);

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
                    'updated_at'       => now()
                ]);

                Cache::put("order_{$request->orderId}", true, now()->addMinutes(30));

                dispatch(new \App\Jobs\GameWalletJop($type == 1 ? -$coin : $coin));

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

            Cache::put("order_{$request->orderId}", true, now()->addHour());

            $user = User::find($request->uid);
            if (!$user) return $this->json(4005, 'user not found');

            return $this->json(0, 'success', [
                'coin' => $user->di
            ]);
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
