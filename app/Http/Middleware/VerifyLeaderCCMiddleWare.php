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
        // LogHelper::info('LeaderCC Request Timing', [
        //     'url'      => $request->fullUrl(),
        //     'method'   => $request->method(),
        //     'body'     => $request->all(),
        //     'path' => $path,
        // ]);
     
        if (!$key) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing or invalid parameters key',
            ], 400);
        }

        if ($request->has('orderId')) {
            $orderId = $request->orderId;
            if (Cache::has("order_$orderId")) {
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
                return response()->json([
                    'errorCode' => 4006,
                    'errorMsg' => 'Endpoint not allowed for this middleware'
                ], 400);
        }


        Log::channel('daily')->info('===== LeaderCC FULL DEBUG START =====');

$key = config('games.leader_CC_game_key');
$token = $request->input('token');
$roomId = $request->input('roomId');
$uid = $request->input('uid');
$gameId = $request->input('gameId');

Log::channel('daily')->info('ENV DEBUG', [
    'app_env' => config('app.env'),
    'app_debug' => config('app.debug'),
    'php_version' => phpversion(),
]);

Log::channel('daily')->info('KEY DEBUG', [
    'value' => $key,
    'length' => strlen($key),
    'hex' => bin2hex($key),
]);

Log::channel('daily')->info('REQUEST RAW DEBUG', [
    'full_url' => $request->fullUrl(),
    'method' => $request->method(),
    'content_type' => $request->header('Content-Type'),
    'raw_body' => $request->getContent(),
]);

Log::channel('daily')->info('FIELDS DEBUG', [
    'gameId' => [
        'value' => $gameId,
        'length' => strlen($gameId),
        'hex' => bin2hex($gameId),
    ],
    'uid' => [
        'value' => $uid,
        'length' => strlen($uid),
        'hex' => bin2hex($uid),
    ],
    'token' => [
        'value' => $token,
        'length' => strlen($token),
        'hex' => bin2hex($token),
    ],
    'roomId' => [
        'value' => $roomId,
        'length' => strlen($roomId),
        'hex' => bin2hex($roomId),
    ],
]);

$rawString =
    $gameId .
    $uid .
    $token .
    $roomId .
    $key;

Log::channel('daily')->info('SIGNATURE BUILD DEBUG', [
    'raw_string' => $rawString,
    'raw_length' => strlen($rawString),
    'raw_hex' => bin2hex($rawString),
    'expected_md5' => md5($rawString),
    'received_sign' => $request->input('sign'),
    'received_sign_length' => strlen($request->input('sign')),
]);

Log::channel('daily')->info('===== LeaderCC FULL DEBUG END =====');

        $expectedSign = md5($rawString);

        if (!hash_equals(strtolower($expectedSign), strtolower($request->input('sign')))) {
        Log::channel('daily')->info('===== LeaderCC hash_equals Not END =====');

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

