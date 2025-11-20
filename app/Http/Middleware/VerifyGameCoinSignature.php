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

        $orderId    = $request->input('orderId');
        $gameId     = $request->input('gameId');
        $roundId    = $request->input('roomId');
        $uid        = $request->input('uid');
        $coin       = $request->input('coin');
        $type       = $request->input('type');
        $rewardType = $request->input('rewardType');
        $winId      = $request->input('winId', '');
        $token      = urldecode($request->input('token'));
        $sign       = $request->input('sign');
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

        $rawString = implode('', [
                $orderId, $gameId, $roundId, $uid, $coin, 
                $type, $rewardType, $token, $winId, $key
            ]);
        $expectedSign = md5($rawString);
        LogHelper::info('Check signature', [
            'rawString' => $rawString,
            'expectedSign' => $expectedSign,
            'clientSign' => $sign
        ]);

        LogHelper::info('Sign Calculation Details', [
            'orderId' => $orderId,
            'gameId' => $gameId,
            'roundId' => $roundId,
            'uid' => $uid,
            'coin' => $coin,
            'type' => $type,
            'rewardType' => $rewardType,
            'token' => $token,
            'winId' => $winId,
            'key' => $key, // تأكد أن الـ key غير فارغ
            'rawString' => $rawString,
            'expectedSign' => $expectedSign,
            'receivedSign' => $sign
        ]);

        if (strtolower($expectedSign) !== strtolower($sign)) {
            return response()->json([
                'errorCode' => 10004,
                'errorMsg'  => 'Verify signature fail'
            ], 400);
        }
        
        return $next($request);
    }
}

