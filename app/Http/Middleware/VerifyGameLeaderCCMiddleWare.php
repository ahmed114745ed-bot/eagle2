<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cache;
use Closure;
use Illuminate\Http\Request;


class VerifyGameLeaderCCMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // $key = config('games.leader_CC_game_key');
        // if (!$key) {
        //     return response()->json([
        //         'errorCode' => 4005,
        //         'errorMsg'  => 'Missing or invalid parameters key',

        //     ], 200);
        // }
        // if ($request->has('orderId')) {
        //     $orderId = $request->orderId;
        //     if (Cache::has("order_$orderId")) {
        //         return response()->json([
        //             'errorCode' => 10003,
        //             'message'   => 'Order already exists'
        //         ], 200);
                
        //     }
        // }

        return $next($request);
    }
}
