<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyGameCoinSignature
{
    public function handle(Request $request, Closure $next)
    {
         Log::info('Middleware Request Details', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        // $orderId     = $request->input('orderId');
        // $gameId      = $request->input('gameId');
        // $roundId     = $request->input('roundId');
        // $uid         = $request->input('uid');
        // $coin        = $request->input('coin');
        // $type        = $request->input('type');
        // $rewardType  = $request->input('rewardType');
        // $winId       = $request->input('winId', '');
        // $token       = $request->input('token');
        // $sign        = $request->input('sign');
        
        // $key = config('games.leader_CC_game_key'); 
        
        // if (
        //     !$orderId || !$gameId || !$roundId || !$uid ||
        //     !$coin || !$rewardType || !$type || !$sign || !$token
        // ) {
        //     return response()->json([
        //         'errorCode' => 4005,
        //         'errorMsg'  => 'Missing signature parameters'
        //     ], 400);
        // }
        
        // $rawString = 
        //     (string)$orderId
        //     . (string)$gameId
        //     . (string)$roundId
        //     . (string)$uid
        //     . (string)$coin
        //     . (string)$type
        //     . (string)$rewardType
        //     . (string)$token
        //     . (string)$winId
        //     . (string)$key;
        // $expectedSign = md5($rawString);
        
        // if (!hash_equals(strtolower($expectedSign), strtolower($sign))) {
        //     return response()->json([
        //         'errorCode' => 10004,
        //         'errorMsg'  => 'Verify signature fail'
        //     ], 400);
        // }
        
        return $next($request);
    }
}

