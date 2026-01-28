<?php

namespace App\Http\Middleware;

use App\Helpers\LogHelper;
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
        $key = config('games.leader_CC_game_key');
         LogHelper::info('LeaderCC Request Timing', [
             'url'      => $request->fullUrl(),
             'method'   => $request->method(),
             'body'     => $request->all(),
             'path' => $path,
         ]);


     
        if (!$key) {
            Log::error('VerifyLeaderCC: Missing game key');
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing or invalid parameters key',
            ], 400);
        }

        if ($request->has('orderId')) {
            $orderId = $request->orderId;
            if (Cache::has("order_$orderId")) {
                Log::error('VerifyLeaderCC: Duplicate orderId', ['orderId' => $orderId]);
                return response()->json([
                    'errorCode' => 10003,
                    'errorMsg'  => 'Order already exists'
                ], 400);
            }
        }
        if ($request->has('token')) {
            $token = $request->token;
            $userId = $this->findUserByToken($token);
            if (!$userId) {
                Log::error('VerifyLeaderCC: User not found by token', ['token' => $token]);
                return response()->json([
                    'errorCode' => 10003,
                    'errorMsg'  => 'user not found'
                ], 400);
            }
        }


        switch ($path) {
            case 'leader-cc-game/change-balance':
                $requiredParams = ['orderId','gameId','roundId','uid','coin','type','rewardType','token','sign'];
                foreach ($requiredParams as $p) {
                    if (!$request->has($p)) {
                        Log::error('VerifyLeaderCC: Missing param for change-balance', ['missing' => $p, 'body' => $request->all()]);
                        return response()->json([
                            'errorCode' => 4005,
                            'errorMsg' => 'Missing signature parameters'
                        ], 400);
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
            case 'leader-cc-game/make-up-orders':
                $requiredParams = ['gameId','uid','token','roomId','sign'];
                foreach ($requiredParams as $p) {
                    if (!$request->has($p)) {
                        Log::error('VerifyLeaderCC: Missing param for get-user-info/make-up-orders', ['missing' => $p, 'body' => $request->all()]);
                        return response()->json([
                            'errorCode' => 4005,
                            'errorMsg' => 'Missing signature parameters'
                        ], 400);
                    }
                }
                $rawString =
                    $request->gameId .
                    $request->uid .
                    $request->token .
                    $request->roomId .
                    $key;
                break;

            default:
                Log::error('VerifyLeaderCC: Endpoint not allowed', ['path' => $path]);
                return response()->json([
                    'errorCode' => 4006,
                    'errorMsg' => 'Endpoint not allowed for this middleware'
                ], 400);
        }

        $expectedSign = md5($rawString);

        if (!hash_equals(strtolower($expectedSign), strtolower($request->input('sign')))) {
            Log::error('VerifyLeaderCC: Signature mismatch', [
                'rawString' => $rawString,
                'expectedSign' => $expectedSign,
                'receivedSign' => $request->input('sign'),
            ]);
            return response()->json([
                'errorCode' => 10004,
                'errorMsg' => 'Verify signature fail'
            ], 400);
        }

      
    
            $response = $next($request);
    
        } catch (\Throwable $e) {
    
            $duration = microtime(true) - $start;
            return response()->json([
                'errorCode' => 5000,
                'errorMsg'  => 'Internal server error',
                'details'   => $e->getMessage(),
            ], 500);
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

