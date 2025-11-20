<?php

namespace App\Http\Middleware;

use App\Helpers\LogHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;

class VerifyGameCoinSignature
{
    public function handle(Request $request, Closure $next)
    {
        
        LogHelper::info('Middleware Request Details', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]); 

        $orderId    = (string)$request->input('orderId');
        $gameId     = (string)$request->input('gameId');
        $roundId    = (string)$request->input('roundId');
        $uid        = (string)$request->input('uid');
        $coin       = (string)$request->input('coin');
        $type       = (string)$request->input('type');
        $rewardType = (string)$request->input('rewardType');
        $winId      = (string)$request->input('winId', '');
        $token      = "10156%7CoYeE836AMI9gsvsYY53Ypsbz5AcnxpGGAqx3ZpVcba51274f";
        $sign       = strtolower($request->input('sign'));
        $key        = config('games.leader_CC_game_key');
        
        LogHelper::info('Middleware Request Details {token}', [
            'token' => $token,
        ]); 
        
        if (
            !$orderId || !$gameId || !$roundId || !$uid ||
            !$coin || !$rewardType || !$type || !$sign || !$token
        ) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing signature parameters'
            ], 400);
        }
     // String sign = md5(orderId + gameId + roundId + uid + coin + type + rewardType + token + winId + key); => *change-balance*

        $rawString = 
            (string)$orderId
            . (string)$gameId
            . (string)$roundId
            . (string)$uid
            . (string)$coin
            . (string)$type
            . (string)$rewardType
            . (string)$token
            . (string)$winId
            . (string)$key;
        $expectedSign = md5($rawString);
        LogHelper::info('Check signature', [
            'rawString' => $rawString,
            'expectedSign' => $expectedSign,
            'clientSign' => $sign
        ]);
        if (!hash_equals(strtolower($expectedSign), strtolower($sign))) {
            return response()->json([
                'errorCode' => 10004,
                'errorMsg'  => 'Verify signature fail'
            ], 400);
        }
        
        return $next($request);
    }
}

