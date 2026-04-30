<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Models\Room;
use App\Models\User;
use App\Models\GameWallet;
use App\Models\GameSession;
use App\Models\GameSeat;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Helpers\UserCoinLogHelper;
use App\Enums\UserCoinLogType;

/**
 * LeaderCC Game Controller
 *
 * Handles Section 4 of the LeaderCC integration:
 * Game Server → APP Server interfaces.
 *
 * All endpoints that receive an orderId implement deduplication:
 * - If the same orderId is received again (retry), return success without re-processing.
 * - The game server retries up to 3 times by default.
 *
 * Endpoints:
 *  4.1  getMicrophoneInfo  — Get microphone seat information for a room
 *  4.2  userInformation    — Query user information
 *  4.3  sitDown            — Confirm whether a user can sit down
 *  4.4  standUp            — Synchronize room status when user stands up
 *  4.5  gameStart          — Notification when a game starts
 *  4.6  gameEnd            — Notification when a game ends
 *       updateGameCoin     — Update (deduct/add) game coins
 *       makeUpOrders       — Retry/make-up failed coin orders
 */
class LeaderCCgameController extends Controller
{
    // ─────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────

    private function json(int $errorCode = 0, string $message = 'success', $data = [])
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
            Log::error('LeaderCC Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return $this->json(500, 'Server error: ' . $e->getMessage());
        }
    }

    /**
     * Check if an orderId already exists in GameSession (dedup).
     */
    private function isDuplicateSession(string $orderId): bool
    {
        return GameSession::where('orderId', $orderId)->exists();
    }

    /**
     * Check if an orderId already exists in GameSeat (dedup).
     */
    private function isDuplicateSeat(string $orderId): bool
    {
        return GameSeat::where('orderId', $orderId)->exists();
    }

    // ─────────────────────────────────────────────────
    //  4.1  Get Microphone Seat Information
    // ─────────────────────────────────────────────────

    /**
     * Returns mic seat info for a room.
     * Sign: md5(orderId + gameId + roomId + uid + key)
     */
    public function getMicrophoneInfo(Request $request)
    {
        return $this->safe(function () use ($request) {

            $room = Room::with(['microphones.user.profile'])->find($request->roomId);

            if (!$room) {
                return $this->json(5002, 'Room does not exist');
            }

            $list = $room->microphones
                ->filter(fn($mic) => $mic->user_id > 0)
                ->map(fn($mic) => [
                    'uid'      => (string) $mic->user_id,
                    'nickname' => $mic->user->name ?? '',
                    'avatar'   => getImagePath($mic->user->profile->avatar ?? ''),
                    'location' => (int) $mic->position,
                ])
                ->values()
                ->toArray();

            return $this->json(0, 'success', ['list' => $list]);
        });
    }

    // ─────────────────────────────────────────────────
    //  4.2  Query User Information
    // ─────────────────────────────────────────────────

    /**
     * Returns user info: UID, nickname, avatar, coins, VIP level, water rate.
     * Sign: md5(gameId + uid + token + key)
     */
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
                return $this->json(4005, 'User not found');
            }

            return $this->json(0, 'success', [
                'uid'      => (string) $user->id,
                'nickname' => $user->name,
                'avatar'   => getImagePath($user->profile->avatar ?? ''),
                'coin'     => $user->di,
                'vipLevel' => $user->UserVip->level ?? 0,
                'water'    => @$user->gamePercentage->percentageGame->percentage_game ?? 2.00,
            ]);
        });
    }

    // ─────────────────────────────────────────────────
    //  4.3  Sit Down in Room
    // ─────────────────────────────────────────────────

    /**
     * Confirm whether a user can sit down in a game room.
     * Deduplicates via GameSeat.orderId.
     * Sign: md5(orderId + gameId + roomId + uid + location + key)
     */
    public function sitDown(Request $request)
    {
        return $this->safe(function () use ($request) {

            // ── Dedup: if this orderId was already processed, return success ──
            if ($this->isDuplicateSeat($request->orderId)) {
                return $this->json(0, 'success', 'success');
            }

            $user = User::select('id', 'di', 'can_play')->find($request->uid);

            if (!$user) {
                return $this->json(4005, 'User not found');
            }

            if (!$user->can_play) {
                return $this->json(4003, 'Player login prohibited');
            }

            $room = Room::find($request->roomId);
            if (!$room) {
                return $this->json(5002, 'Room does not exist');
            }

            // In paid mode, check sufficient coins
            $fees = (float) ($request->fees ?? 0);
            if ($fees > 0 && $user->di < $fees) {
                return $this->json(5204, 'Insufficient game currency');
            }

            if ($err = $this->checkWallet($request)) {
                return $err;
            }

            // ── Record the seat action ──
            GameSeat::create([
                'user_id'  => $request->uid,
                'room_id'  => $request->roomId,
                'game_id'  => $request->gameId,
                'orderId'  => $request->orderId,
                'location' => $request->location,
                'fees'     => $fees,
            ]);

            return $this->json(0, 'success', 'success');
        });
    }

    // ─────────────────────────────────────────────────
    //  4.4  Stand Up in Room
    // ─────────────────────────────────────────────────

    /**
     * Synchronize room status when a user stands up.
     * Deduplicates via GameSeat.orderId.
     * Sign: md5(orderId + gameId + roomId + uid + location + key)
     */
    public function standUp(Request $request)
    {
        return $this->safe(function () use ($request) {

            // ── Dedup ──
            if ($this->isDuplicateSeat($request->orderId)) {
                return $this->json(0, 'success', 'success');
            }

            $user = User::find($request->uid);
            if (!$user) {
                return $this->json(4005, 'User not found');
            }

            // ── Record the stand-up action ──
            GameSeat::create([
                'user_id'  => $request->uid,
                'room_id'  => $request->roomId,
                'game_id'  => $request->gameId,
                'orderId'  => $request->orderId,
                'location' => $request->location,
                'fees'     => 0,
            ]);

            return $this->json(0, 'success', 'success');
        });
    }

    // ─────────────────────────────────────────────────
    //  4.5  Game Start
    // ─────────────────────────────────────────────────

    /**
     * Called when a game starts.
     * Deduplicates via GameSession.orderId.
     * Sign: md5(orderId + gameId + roomId + uid + key)
     */
    public function gameStart(Request $request)
    {
        return $this->safe(function () use ($request) {

            // ── Dedup ──
            if ($this->isDuplicateSession($request->orderId)) {
                return $this->json(0, 'success', 'success');
            }

            $room = Room::find($request->roomId);
            if (!$room) {
                return $this->json(5002, 'Room does not exist');
            }

            // ── Record the game session ──
            GameSession::create([
                'orderId'     => $request->orderId,
                'game_id'     => $request->gameId,
                'room_id'     => $request->roomId,
                'owner_uid'   => $request->uid,
                'player_list' => $request->playerList ?? [],
                'started_at'  => now(),
            ]);

            Log::info('LeaderCC Game Started', [
                'orderId'    => $request->orderId,
                'gameId'     => $request->gameId,
                'roomId'     => $request->roomId,
                'uid'        => $request->uid,
                'playerList' => $request->playerList ?? [],
            ]);

            return $this->json(0, 'success', 'success');
        });
    }

    // ─────────────────────────────────────────────────
    //  4.6  Game End
    // ─────────────────────────────────────────────────

    /**
     * Called when a game ends.
     * Deduplicates via GameSession.orderId.
     * Sign: md5(orderId + gameId + roomId + uid + key)
     */
    public function gameEnd(Request $request)
    {
        return $this->safe(function () use ($request) {

            // ── Dedup ──
            if ($this->isDuplicateSession($request->orderId)) {
                return $this->json(0, 'success', 'success');
            }

            // ── Record the game end ──
            GameSession::create([
                'orderId'      => $request->orderId,
                'game_id'      => $request->gameId,
                'room_id'      => $request->roomId,
                'owner_uid'    => $request->uid,
                'rankList'     => $request->rankList ?? [],
                'reason'       => $request->reason ?? 'normal',
                'room_destroy' => (bool) ($request->roomDestroy ?? false),
                'ended_at'     => now(),
            ]);

            Log::info('LeaderCC Game Ended', [
                'orderId'     => $request->orderId,
                'gameId'      => $request->gameId,
                'roomId'      => $request->roomId,
                'uid'         => $request->uid,
                'rankList'    => $request->rankList ?? [],
                'reason'      => $request->reason ?? 'normal',
                'roomDestroy' => $request->roomDestroy ?? 0,
            ]);

            return $this->json(0, 'success', 'success');
        });
    }

    // ─────────────────────────────────────────────────
    //  Update Game Coin (change-balance)
    // ─────────────────────────────────────────────────

    /**
     * Deduct or add game coins for a user.
     * type=1 → deduct, type=2 → add
     * Deduplicates via Cache + coin_game_users.order_id
     */
    public function updateGameCoin(Request $request)
    {
        return $this->safe(function () use ($request) {

            $required = ['orderId', 'gameId', 'roundId', 'uid', 'coin', 'type', 'rewardType', 'token', 'sign'];
            $missing = array_filter($required, fn($r) => !$request->filled($r) && $request->input($r) !== "0");
            if ($missing) return $this->json(4005, 'Invalid params');

            if ($err = $this->checkWallet($request)) {
                return $err;
            }

            $type = (int) $request->type;
            if (!in_array($type, [1, 2])) {
                return $this->json(4005, 'Invalid type');
            }

            // ── Dedup via Cache ──
            if (Cache::has("order_{$request->orderId}")) {
                $user = User::find($request->uid);
                return $this->json(0, 'success', ['coins' => $user->di ?? 0]);
            }

            return DB::transaction(function () use ($request, $type) {

                $user = User::lockForUpdate()->with([
                    'profile:id,user_id,avatar',
                    'nowGame:id,image',
                    'nowRoom:id,uid',
                ])->find($request->uid);

                if (!$user) return $this->json(4005, 'User not found');

                $coin = abs((int) $request->coin);

                if ($type == 1 && $user->di < $coin) {
                    return $this->json(5204, 'Insufficient game currency');
                }

                $amountBefore = $user->di;
                $user->di = $type == 1 ? ($user->di - $coin) : ($user->di + $coin);
                $user->save();

                $sign = $type == 1 ? -1 : 1;
                UserCoinLogHelper::logByType(
                    $user->id,
                    $sign * $coin,
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
                    'updated_at'       => now(),
                ]);

                Cache::put("order_{$request->orderId}", true, now()->addMinutes(30));

                dispatch(new \App\Jobs\GameWalletJop($type == 1 ? -$coin : $coin));

                // Broadcast big win to all rooms
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
                            'gImage'  => @$user->nowGame?->image,
                        ],
                    ];

                    $json = json_encode($d);
                    dispatchJobToQueue(
                        new AllOpeningRoomsZegoRequest($json, $user->id, $roomId, false),
                        'heavyProcessing'
                    );
                }

                return $this->json(0, 'success', ['coins' => $user->di]);
            });
        });
    }

    // ─────────────────────────────────────────────────
    //  Make-Up Orders (retry failed orders)
    // ─────────────────────────────────────────────────

    /**
     * Retry a failed order — only adds coins (compensation).
     * Deduplicates via Cache + coin_game_users.order_id
     */
    public function makeUpOrders(Request $request)
    {
        return $this->safe(function () use ($request) {

            $validator = Validator::make($request->all(), [
                'orderId'    => 'required',
                'gameId'     => 'required',
                'roundId'    => 'required',
                'uid'        => 'required',
                'coin'       => 'required|numeric',
                'rewardType' => 'required|integer',
                'sign'       => 'required',
            ]);

            if ($validator->fails()) {
                return $this->json(4005, 'Missing or invalid parameters', $validator->errors());
            }

            // ── Dedup ──
            if (Cache::has("order_{$request->orderId}")) {
                $user = User::find($request->uid);
                return $this->json(0, 'success', ['coin' => $user->di ?? 0]);
            }

            return DB::transaction(function () use ($request) {
                $user = User::lockForUpdate()->find($request->uid);
                if (!$user) return $this->json(4005, 'User not found');

                $coin = abs((int) $request->coin);
                $amountBefore = $user->di;
                $user->di += $coin;
                $user->save();

                UserCoinLogHelper::logByType(
                    $user->id,
                    $coin,
                    $amountBefore,
                    UserCoinLogType::COIN_GAME,
                    null,
                );

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

                Cache::put("order_{$request->orderId}", true, now()->addHour());
                dispatch(new \App\Jobs\GameWalletJop($coin));

                return $this->json(0, 'success', ['coin' => $user->di]);
            });
        });
    }

    // ─────────────────────────────────────────────────
    //  Wallet Guard
    // ─────────────────────────────────────────────────

    public function checkWallet($request)
    {
        if ($request->type == 1 && $this->checkLoseWallet($request->coin)) {
            return $this->json(4005, 'Game not available');
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
