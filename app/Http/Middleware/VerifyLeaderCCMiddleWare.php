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
        
        // ========== DETAILED LOGGING START ==========
        Log::channel('daily')->info('========== LeaderCC Request START ==========');
        Log::channel('daily')->info('LeaderCC: Request Details', [
            'timestamp' => now()->toDateTimeString(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'path' => $path,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        Log::channel('daily')->info('LeaderCC: Request Body', [
            'all_params' => $request->all(),
            'gameId' => $request->input('gameId'),
            'uid' => $request->input('uid'),
            'token' => $request->input('token'),
            'roomId' => $request->input('roomId'),
            'sign' => $request->input('sign'),
            'orderId' => $request->input('orderId'),
            'coin' => $request->input('coin'),
            'type' => $request->input('type'),
            'roundId' => $request->input('roundId'),
            'rewardType' => $request->input('rewardType'),
            'winId' => $request->input('winId'),
        ]);
        
        Log::channel('daily')->info('LeaderCC: Config', [
            'config_key_name' => 'games.leader_CC_game_key',
            'config_key_value' => $key,
            'config_key_length' => $key ? strlen($key) : 0,
            'config_key_exists' => !empty($key),
        ]);
        // ========== DETAILED LOGGING END ==========


     
        if (!$key) {
            Log::channel('daily')->error('LeaderCC: FAILED - Missing game key', [
                'config_checked' => 'games.leader_CC_game_key',
                'value' => $key,
            ]);
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing or invalid parameters key',
            ], 400);
        }

        if ($request->has('orderId')) {
            $orderId = $request->orderId;
            $cacheKey = "order_$orderId";
            $cacheExists = Cache::has($cacheKey);
            
            Log::channel('daily')->info('LeaderCC: Order Check', [
                'orderId' => $orderId,
                'cache_key' => $cacheKey,
                'cache_exists' => $cacheExists,
            ]);
            
            if ($cacheExists) {
                Log::channel('daily')->error('LeaderCC: FAILED - Duplicate orderId', ['orderId' => $orderId]);
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
            Log::channel('daily')->info('LeaderCC: Token received', [
                'token' => $token,
                'token_length' => strlen($token),
            ]);
        }


        Log::channel('daily')->info('LeaderCC: Checking endpoint', ['path' => $path]);
        
        switch ($path) {
            case 'leader-cc-game/change-balance':
                $requiredParams = ['orderId','gameId','roundId','uid','coin','type','rewardType','token','sign'];
                Log::channel('daily')->info('LeaderCC: Endpoint matched - change-balance', [
                    'required_params' => $requiredParams,
                ]);
                
                foreach ($requiredParams as $p) {
                    $hasParam = $request->has($p);
                    $paramValue = $request->input($p);
                    Log::channel('daily')->info("LeaderCC: Param check - $p", [
                        'param' => $p,
                        'has_param' => $hasParam,
                        'value' => $paramValue,
                    ]);
                    
                    if (!$hasParam) {
                        Log::channel('daily')->error('LeaderCC: FAILED - Missing param for change-balance', [
                            'missing' => $p,
                            'all_params' => $request->all(),
                        ]);
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
                    $request->input('roomId', "") .
                    $key;
                    
                Log::channel('daily')->info('LeaderCC: Building signature for change-balance', [
                    'orderId' => $request->orderId,
                    'gameId' => $request->gameId,
                    'roundId' => $request->roundId,
                    'uid' => $request->uid,
                    'coin' => $request->coin,
                    'type' => $request->type,
                    'rewardType' => $request->rewardType,
                    'token' => $request->token,
                    'winId' => $request->input('winId', ""),
                    'roomId' => $request->input('roomId', ""),
                    'key' => $key,
                    'rawString' => $rawString,
                ]);
                break;

            case 'leader-cc-game/get-user-info':
            case 'leader-cc-game/make-up-orders':
                $requiredParams = ['gameId','uid','token','roomId','sign'];
                Log::channel('daily')->info('LeaderCC: Endpoint matched - ' . $path, [
                    'required_params' => $requiredParams,
                ]);
                
                foreach ($requiredParams as $p) {
                    $hasParam = $request->has($p);
                    $paramValue = $request->input($p);
                    Log::channel('daily')->info("LeaderCC: Param check - $p", [
                        'param' => $p,
                        'has_param' => $hasParam,
                        'value' => $paramValue,
                    ]);
                    
                    if (!$hasParam) {
                        Log::channel('daily')->error('LeaderCC: FAILED - Missing param', [
                            'endpoint' => $path,
                            'missing' => $p,
                            'all_params' => $request->all(),
                        ]);
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
                    
                Log::channel('daily')->info('LeaderCC: Building signature for ' . $path, [
                    'gameId' => $request->gameId,
                    'uid' => $request->uid,
                    'token' => $request->token,
                    'roomId' => $request->roomId,
                    'key' => $key,
                    'rawString' => $rawString,
                ]);
                break;

            default:
                Log::channel('daily')->error('LeaderCC: FAILED - Endpoint not allowed', [
                    'path' => $path,
                    'allowed_paths' => [
                        'leader-cc-game/change-balance',
                        'leader-cc-game/get-user-info',
                        'leader-cc-game/make-up-orders',
                    ],
                ]);
                return response()->json([
                    'errorCode' => 4006,
                    'errorMsg' => 'Endpoint not allowed for this middleware'
                ], 400);
        }

        $expectedSign = md5($rawString);
        $receivedSign = $request->input('sign');
        $signMatch = hash_equals(strtolower($expectedSign), strtolower($receivedSign ?? ''));
        
        Log::channel('daily')->info('LeaderCC: Signature Verification', [
            'rawString' => $rawString,
            'rawString_length' => strlen($rawString),
            'expectedSign' => $expectedSign,
            'receivedSign' => $receivedSign,
            'receivedSign_lowercase' => strtolower($receivedSign ?? ''),
            'expectedSign_lowercase' => strtolower($expectedSign),
            'match' => $signMatch,
        ]);

        
        if (!$signMatch) {
            Log::channel('daily')->error('LeaderCC: FAILED - Signature mismatch', [
                'path' => $path,
                'rawString' => $rawString,
                'expectedSign' => $expectedSign,
                'receivedSign' => $receivedSign,
                'all_params' => $request->all(),
            ]);
            return response()->json([
                'errorCode' => 10004,
                'errorMsg' => 'Verify signature fail'
            ], 400);
        }
        
        Log::channel('daily')->info('LeaderCC: SUCCESS - Signature verified, proceeding to controller');
        Log::channel('daily')->info('========== LeaderCC Request END ==========');

      
    
            $response = $next($request);
    
        } catch (\Throwable $e) {
    
            $duration = microtime(true) - $start;
            Log::channel('daily')->error('LeaderCC: EXCEPTION', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'errorCode' => 5000,
                'errorMsg'  => 'Internal server error',
                'details'   => $e->getMessage(),
            ], 500);
        }
    
        $duration = microtime(true) - $start;
        Log::channel('daily')->info('LeaderCC: Request completed', [
            'duration_ms' => round($duration * 1000, 2),
        ]);
    
    
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

