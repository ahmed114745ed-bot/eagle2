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
        LogHelper::info('Check signature request', [
            'request' => $request->all(),
           
        ]);

        $orderId    = (string)$request->input('orderId');
        $gameId     = (string)$request->input('gameId');
        $roundId    = (string)$request->input('roundId');
        $uid        = (string)$request->input('uid');
        $coin       = (integer)$request->input('coin');
        $type       = (integer)$request->input('type');
        $rewardType = (integer)$request->input('rewardType');
        $winId      = $request->input('winId', "");
        $token      = $request->input('token');
        $sign       = $request->input('sign');
        $key        = config('games.leader_CC_game_key');
    
        if (
            !$orderId || !$gameId || !$roundId || !$uid ||
            !$coin || !$rewardType || !$type || !$sign || !$token
        ) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing signature parameters'
            ], 400);
        }
        // String sign = md5(orderId + gameId + roundId + uid + coin + type + rewardType + token + winId + key);

        $rawString = $orderId . $gameId . $roundId . $uid . $coin . $type . $rewardType . $token . $winId . $key;
        $expectedSign = md5($rawString);

    

        if (strtolower($expectedSign) !== strtolower($sign)) {
            return response()->json([
                'errorCode' => 10004,
                'errorMsg'  => 'Verify signature fail'
            ], 400);
        }
        
        return $next($request);
    }
}

