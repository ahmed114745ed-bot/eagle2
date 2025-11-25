<?php

namespace App\Http\Middleware;

use App\Helpers\LogHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;

class VerifyGameLeaderCCUserInformationMiddleWare
{
    public function handle(Request $request, Closure $next)
    {
        /*  LogHelper::info('Middleware Request Details', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]); */
        // String sign = md5(gameId + uid + token + roomId + key); => *user information*

        $gameId      = $request->input('gameId');
        $uid         = $request->input('uid');
        $token       = $request->input('token');
        $roomId     = $request->input('roomId');
        $sign        = $request->input('sign');

        
        $key = config('games.leader_CC_game_key'); 
        
        if (!isset($roomId, $gameId, $uid, $sign)) {
            
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing signature parameters'
            ], 200);
        }
        
        $rawString = 
             (string)$gameId
            . (string)$uid
            . (string)$token
            . (string)$roomId
            . (string)$key;

        $expectedSign = md5($rawString);
        
        if (!hash_equals(strtolower($expectedSign), strtolower($sign))) {
            return response()->json([
                'errorCode' => 10004,
                'errorMsg'  => 'Verify signature fail'
            ], 200);
        }
        
        return $next($request);
    }
}

