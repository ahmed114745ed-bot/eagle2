<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserCoinLogType;
use App\Helpers\Common;
use App\Helpers\LogHelper;
use App\Helpers\UserCoinLogHelper;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Jobs\LogUserCumulativeCoinProfit;
use App\Models\Room;
use DB;
use App\Models\User;
use App\Models\GameWallet;
use App\Jobs\GameWalletJop;
use App\Models\CoinGameUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Services\BaishunGameServices;

class BaishunGameController extends Controller
{

    public function changeBalance(Request $request)
    {
        $errorExists = $this->checkWallet($request);
        if ($errorExists) return response()->json($errorExists);

        $id = $this->findUserByToken($request->code ?? $request->ss_token);

        if (!$id) {
            $responseArray = [
                'code' => 1,
                'message' => 'user not found',
                'unique_id' => 0,
            ];

            return response()->json($responseArray);
        }

        $userDi = 0;

        try {
            DB::transaction(function () use ($id, $request, &$userDi) {
                // Lock user row to prevent race conditions
                $user = DB::table('users')->where('id', $id)->lockForUpdate()->first();

                if (!$user) {
                    throw new \Exception('User not found');
                }

                $userDi = $user->di;

                // Check if order_id already used in coin_game_users
                // $orderExists = DB::table('coin_game_users')
                //     ->where('order_id', $request->order_id)
                //     ->lockForUpdate()
                //     ->exists();

                // if ($orderExists) {
                //     throw new \RuntimeException('duplicate_order');
                // }

                if ($request->currency_diff < 0 && $userDi < abs($request->currency_diff)) {
                    throw new \RuntimeException('insufficient');
                }

                $amountBefore = $user->di;

                $helperAmount = $request->currency_diff > 0 ? $request->currency_diff : 0;

                UserCoinLogHelper::logByType(
                    $user->id,
                    $request->currency_diff,
                    $amountBefore,
                    UserCoinLogType::COIN_GAME,
                    null,
                    $helperAmount
                );


                DB::table('users')->where('id', $id)->update([
                    'di' => DB::raw('di + ' . (int) $request->currency_diff)
                ]);

                $userDi += (int) $request->currency_diff;

                // Insert into coin_game_users after all checks
                $gameId = User::withoutAppends()->where('id', $id)->value('game_id');

                DB::table('coin_game_users')->insert([
                    'user_id' => $id,
                    'coins' => abs($request->currency_diff),
                    'app_profit_coins' => abs($request->currency_diff),
                    'type' => $request->currency_diff >= 0,
                    'game_id' => $gameId,
                    'round_id' => $request->game_round_id,
                    'order_id' => $request->order_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });

        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'insufficient') {
                $responseArray = [
                    'code' => 1008,
                    'message' => 'failed',
                    'unique_id' => (string) $id,
                    'data' => [
                        'currency_balance' => $userDi
                    ]
                ];

                return response()->json($responseArray);
            }

            return response()->json([
                'code' => 1,
                'message' => 'user not found',
                'unique_id' => (string) $id,
            ]);
        }

        $type = $request->currency_diff >= 0;

        dispatch(new GameWalletJop($request->currency_diff));


        $responseArray = [
            'code' => 0,
            'message' => 'succeed',
            'unique_id' => (string) $id,
            'data' => [
                'currency_balance' => floatval($userDi)
            ]
        ];

        if ($type && (int) $request->currency_diff >= Common::getConfig('game_map_win_coins')) {
            $user = User::with(['profile', 'nowGame'])->find($id);
            $room = Room::withoutAppends()->select(['id'])->where("uid", $user->now_room_uid)->first();

            $d = [
                "messageContent" => [
                    "message" => "SBG",
                    "event" => "baishun.game.event",
                    'uImage'  => $user->profile?->avatar ?? 0,
                    'uName'   => $user->name ?? '',
                    'uId'     => $user->id ?? 0,
                    'coins'   => numToStringNew((int) $request->currency_diff),
                    "gImage"  => @$user->nowGame?->image
                ]
            ];

            $json = json_encode($d);
            dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $user->id, $room?->id, false), 'heavyProcessing');
        }

        return response()->json($responseArray);
    }

    /**public function changeBalance(Request $request)
    {

//        $allowedUsers = [1177];
//        $isAllow = !in_array($id, $allowedUsers);

        $errorExists = $this->checkWallet($request);

        if ($errorExists) return response()->json($errorExists);
        // $unique_id = $this->getUniqueId($request->signature_nonce,$request->signature,$request->timestamp);

        $id = $this->findUserByToken($request->code ?? $request->ss_token);

        if (!$id ) {
            $responseArray = [
                'code' => 1,
                'message' => 'user not found',
                'unique_id' => 0,

            ];

            return response()->json($responseArray);
        }
        $user = User::query()
            ->where('id', $id)
            ->first();

        $userDi = $user->di;
        if ($request->currency_diff < 0) {
            if ($userDi < (-1 * $request->currency_diff)) {
                $responseArray = [
                    'code' => 1,
                    'message' => 'failed',
                    'unique_id' => (string) $id,
                    'data' => [
                        'currency_balance' => $userDi
                    ]
                ];

                return response()->json($responseArray);
            }
        }

        DB::table('users')->where('id', $id)->lockForUpdate()->update([
            'di' => DB::raw('di + ' . (int) $request->currency_diff)
        ]);

        if ($request->currency_diff < 0) {
            $type = false;
        } else {
            $type = true;
        }

        $userDi += (int) $request->currency_diff;
        $gameId = User::withoutAppends()->where('id', $id)->value('game_id');
        dispatch(new GameWalletJop($request->currency_diff));
        CoinGameUser::create(['user_id' => $id,
         'coins' => abs($request->currency_diff),
         'app_profit_coins' => abs($request->currency_diff),
         'type' => $type, 'game_id' => @$gameId
        ]);
        $responseArray = [
            'code' => 0,
            'message' => 'succeed',
            'unique_id' => (string) $id,
            'data' => [
                'currency_balance' => floatval($userDi)
            ]
        ];

        if ($type && (int) $request->currency_diff >= Common::getConfig('game_map_win_coins')) {
            $room      = Room::withoutAppends()->select(['id'])->where("uid", $user->now_room_uid)->first();
            $d    = [
                "messageContent" => [
                    "message" => "SBG",
                    'uImage'  => $user->profile?->avatar ?? 0,
                    'uName'   => $user->name ?? '',
                    'uId'     => $user->id ?? 0,
                    'coins'   => (int) $request->currency_diff,
                    "gImage"  => @$user->nowGame?->image
                ]
            ];
            $json = json_encode($d);
            dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $user->id, $room?->id, false), 'heavyProcessing');
        }



        return response()->json($responseArray);
    }

    public function getUserUniqueId()
    {

        $appKey = config('app.baishun_app_key');
        $url = config('app.baishun_server_url');
        $timestamp = time();
        $signatureNonce = generatesignatureNonce();
        $signature = generateSignature($signatureNonce, $appKey, $timestamp);

        $headers = [
            'Content-Type: application/json'
        ];

        $postData = [
            'signature_nonce' => $signatureNonce,
            'timestamp' => $timestamp,
            'signature' => $signature
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);

        if ($data) {
            return $data['unique_id'];
        }

        return;
    }**/

    public function obtianSstoken(Request $request)
    {
        $timestamp = $request->timestamp;
        $signatureNonce = $request->signature_nonce;
        $signature = $request->signature;
        // $unique_id = $this->getUniqueId($signatureNonce,$timestamp,$signature);
        $token = $request->code ?? $request->ss_token;
        $id = $this->findUserByToken($token);

        if (!$id) {
            $responseArray = [
                'code' => 1,
                'message' => 'user not found',
                'unique_id' =>(string) 0,

            ];

            return response()->json($responseArray);
        }
        $data = [
            'code' => 0,
            'message' => 'succeed',
            'unique_id' => (string) $id,
            'data' => [
                'ss_token' => $token,
                'expire_date' => today()->addDay()->timestamp * 1000,
            ]
        ];

        return response()->json($data);
    }

    public function get_user_info(Request $request)
    {

        $userId = $this->findUserByToken($request->code ?? $request->ss_token);
//        $allowedUsers = [1177];
//        $isAllow = !in_array($userId, $allowedUsers);

        $errorExists = $this->checkWallet($request);
        if ($errorExists ) return response()->json($errorExists);

        if (!$userId ) {
            $responseArray = [
                'code' => 1,
                'message' => 'user not found',
                'unique_id' => 0,

            ];

            return response()->json($responseArray);
        }
        /*$pattern = '/id(\d+)/';
        preg_match($pattern, $requestId, $matches);

        if (!empty($matches)) {
            $userId = $matches[1];
        } else {
            return response()->json(500);
        }*/
        //        $unique_id = $this->getUniqueId($request->signature_nonce,$request->timestamp,$request->signature);
        $unique_id = $userId;
        $user = User::find($userId);
        $data = [
            'code' => 0,
            'message' => 'succeed',
            'unique_id' => (string) $unique_id,
            'data' => [
                'user_id' => (string)$userId,
                'user_name' => $user->name,
                'user_avatar' => getImagePath($user->profile->avatar),
                'balance' => $user->di
            ]
        ];


        return response()->json($data);
    }

    public  function getUniqueId($signatureNonce, $timestamp, $signature)
    {
        if (!$signatureNonce || !$timestamp || !$signature) return null;
        $url = config('app.baishun_server_url');

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($url, [
            'signature_nonce' => $signatureNonce,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);
        $data = json_decode($response->getBody(), true);
        if ($data) {
            return $data['unique_id'];
        }
        return null;
    }

    public function getId($id)
    {
        $input = "id1001";
        $number = str_replace(range('a', 'z'), '', $input);
        $number = str_replace(range('A', 'Z'), '', $number);
        return $number;
    }


    public function findUserByToken($token): mixed
    {
        $user = @PersonalAccessToken::findToken($token)->tokenable;
        if ($user) {
            $lastToken = $user->tokens()->where('tokenable_type', User::class)->orderByDesc('id')->first()->token;

            if (strpos($token, '|') !== false) {
                [$id, $token] = explode('|', $token, 2);
            }
            $token = hash('sha256', $token);
            if (!hash_equals($lastToken, $token)) {
                $user = null;
            }
        }

        return @$user->id;
    }

    public function checkWallet($request)
    {
        $gameWallet = GameWallet::filterByMonth()->first();
        $gameUsed = $gameWallet?->used ?? 0;
        $used = $gameUsed + ((($request?->currency_diff ?? 0) < 0) ? ($request->currency_diff * -1) : 0);
        if (!$gameWallet || $used >= $gameWallet->balance) {
            $responseArray = [
                'code' => 1,
                'message' => 'game not available',
                'unique_id' => 0,

            ];

            return response()->json($responseArray);
        }
    }
}

