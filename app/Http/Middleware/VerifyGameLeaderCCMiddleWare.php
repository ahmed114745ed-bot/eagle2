<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyGameLeaderCCMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
         
        $key = config('games.leader_CC_game_key');
        if (!$key) {
            return response()->json([
                'errorCode' => 4005,
                'errorMsg'  => 'Missing or invalid parameters key',

            ], 200);
        }
        if ($request->has('orderId')) {
            $orderId = $request->orderId;
            if (Cache::has("order_$orderId")) {
                return response()->json([
                    'errorCode' => 10003,
                    'message'   => 'Order already exists'
                ], 200);
                
            }
        }

        $response =  $next($request);

        // \App\Helpers\LogHelper::info('VerifyGameLeaderCCMiddleWare Middleware Request Details', [
        //     'url' => $request->fullUrl(),
        //     'method' => $request->method(),
        //     'body' => $request->all(),
        //     'ip' => $request->ip(),
        //     'response_body' => method_exists($response,'getContent') 
        //     ? json_decode($response->getContent(), true) 
        //     : null,            
        //     'headers' => $request->headers->all(),
        // ]);
        return $response;
    }
}
