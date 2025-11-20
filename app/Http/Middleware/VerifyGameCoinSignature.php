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
        $orderId    = (string)$request->input('orderId');
        $gameId     = (string)$request->input('gameId');
        $roundId    = (string)$request->input('roundId');
        $uid        = (string)$request->input('uid');
        $coin       = (integer)$request->input('coin');
        $type       = (integer)$request->input('type');
        $rewardType = (integer)$request->input('rewardType');
        $winId      = $request->input('winId', "");
        $token      = (string)$request->input('token');
        $sign       = $request->input('sign');
        $key        = config('games.leader_CC_game_key');
          LogHelper::info('Middleware - All inputs', [
            'orderId' => $orderId,
            'gameId' => $gameId,
            'roundId' => $roundId,
            'roomId' => $request->input('roomId'),
            'uid' => $uid,
            'coin' => $coin,
            'type' => $type,
            'rewardType' => $rewardType,
            'winId' => $winId,
            'token' => $token,
            'sign' => $sign
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

        $rawString = implode('', [
            $orderId, $gameId, $roundId, $uid, $coin, 
            $type, $rewardType, $token, $winId, $key
        ]);
        
        $expectedSign = md5($rawString);
  LogHelper::info('Middleware -expectedSign', [
            $expectedSign
    ]);
        LogHelper::info('Check signature', [
            'rawString' => $rawString,
            'expectedSign' => $expectedSign,
            'clientSign' => $sign
        ]);

        if (strtolower($expectedSign) !== strtolower($sign)) {
            Log::info("inside ");
            return response()->json([
                'errorCode' => 10004,
                'errorMsg'  => 'Verify signature fail'
            ], 400);
        }
            Log::info("outside ");
        
        return $next($request);
    }
}

