<?php

namespace App\Http\Middleware;

use App\Helpers\Common;
use App\Helpers\LogHelper;
use App\Models\GameProviderSetting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class VerifyLeaderCCMiddleWare
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);

        try {
         
            $path = ltrim(str_replace('api/', '', $request->path()), '/');
            $gameSetting =  Common::getByCode('quantum_nexus');
            $key =  @$gameSetting->app_key ?? '';
            if (!$gameSetting->is_active) {
                return response()->json([
                    'errorCode' => 4005,
                    'errorMsg'  => 'Game is not active now',
                ]);
            }

            if (!$key) {
                return response()->json([
                    'errorCode' => 4005,
                    'errorMsg'  => 'Missing or invalid parameters key',
                ]);
            }

            if ($request->has('orderId')) {
                $orderId = $request->orderId;
                if (Cache::has("order_$orderId")) {
                    return response()->json([
                        'errorCode' => 10003,
                        'errorMsg'  => 'Order already exists'
                    ]);
                }
            }

            if ($request->has('token') && $request->has('uid')) {
                $userId = $this->findUserByToken($request->token);
                if ($userId && $userId != $request->uid) {
                    return response()->json([
                        'errorCode' => 10003,
                        'errorMsg' => 'token/uid mismatch'
                    ]);
                }
                if (!$userId && !\App\Models\User::where('id', $request->uid)->exists()) {
                    return response()->json([
                        'errorCode' => 10003,
                        'errorMsg' => 'user not found'
                    ]);
                }
            }


            switch ($path) {
                case 'leader-cc-game/change-balance':
                    $requiredParams = ['orderId', 'gameId', 'roundId', 'uid', 'coin', 'type', 'rewardType', 'token', 'sign'];
                    foreach ($requiredParams as $p) {
                        if (!$request->has($p)) {
                            return response()->json([
                                'errorCode' => 4005,
                                'errorMsg' => 'Missing signature parameters'
                            ]);
                        }
                    }
                    $rawString =
                        $request->orderId .
                        $request->gameId .
                        $request->roundId .
                        $request->uid .
                        $request->coin .
                        $request->type .
                        $request->rewardType .
                        $request->token .
                        $request->input('winId', "") .
                        $request->roomId .
                        $key;
                    break;

                case 'leader-cc-game/get-user-info':
                    $requiredParams = ['gameId', 'uid', 'token', 'roomId', 'sign'];
                    foreach ($requiredParams as $p) {
                        if (!$request->has($p)) {
                            return response()->json([
                                'errorCode' => 4005,
                                'errorMsg' => 'Missing signature parameters'
                            ]);
                        }
                    }
                    $rawString =
                        $request->gameId .
                        $request->uid .
                        $request->token .
                        $request->roomId .
                        $key;
                    break;

                case 'leader-cc-game/make-up-orders':
                    $requiredParams = ['orderId', 'gameId', 'roundId', 'uid', 'coin', 'rewardType', 'sign'];
                    foreach ($requiredParams as $p) {
                        if (!$request->has($p)) {
                            return response()->json([
                                'errorCode' => 4005,
                                'errorMsg' => 'Missing signature parameters'
                            ]);
                        }
                    }
                    $rawString =
                        $request->orderId .
                        $request->gameId .
                        $request->roundId .
                        $request->uid .
                        $request->coin .
                        $request->rewardType .
                        $request->input('winId', '') .
                        $request->input('roomId', '') .
                        $key;
                    break;

                default:
                    return response()->json([
                        'errorCode' => 4006,
                        'errorMsg' => 'Endpoint not allowed for this middleware'
                    ]);
            }

            $expectedSign = md5($rawString);

            if (!hash_equals(strtolower($expectedSign), strtolower($request->input('sign')))) {
                return response()->json([
                    'errorCode' => 10004,
                    'errorMsg' => 'Verify signature fail'
                ]);
            }


            $response = $next($request);
        } catch (\Throwable $e) {

            $duration = microtime(true) - $start;
            return response()->json([
                'errorCode' => 5000,
                'errorMsg' => 'Internal server error',
                'details' => $e->getMessage(),
            ]);
        }

        $duration = microtime(true) - $start;


        return $response;
    }

    public function findUserByToken($token): mixed
    {
        $token = urldecode($token);
        if (strpos($token, '|') !== false) {
            [$_, $plainToken] = explode('|', $token, 2);
        } else {
            $plainToken = $token;
        }
        $personalToken = PersonalAccessToken::findToken($plainToken);
        if (!$personalToken) {
            return null;
        }
        return $personalToken->tokenable_id;
    }




}
