<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyGameCoinSignature
{
    public function handle(Request $request, Closure $next)
    {
        $orderId     = $request->input('orderId');
        $gameId      = $request->input('gameId');
        $roundId     = $request->input('roundId');
        $uid         = $request->input('uid');
        $coin        = $request->input('coin');
        $rewardType  = $request->input('rewardType');
        $winId       = $request->input('winId', '');
        $sign        = $request->input('sign');

        $key = config('games.leader_CC_game_key'); 

        if (!$orderId || !$gameId || !$roundId || !$uid || !$coin || !$rewardType || !$sign) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing signature parameters'
            ], 400);
        }

        $expectedSign = md5($orderId . $gameId . $roundId . $uid . $coin . $rewardType . $winId . $key);

        if (!hash_equals(strtolower($expectedSign), strtolower($sign))) {
            return response()->json([
                'errorCode' => 10004,
                'errorMsg'  => 'Verify signature fail'
            ], 400);
        }

        return $next($request);
    }
}
