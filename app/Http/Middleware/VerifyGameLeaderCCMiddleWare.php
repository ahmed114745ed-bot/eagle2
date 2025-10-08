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
         if ($request->has('orderId')) {
            $orderId = $request->orderId;
            if (Cache::has("order_$orderId")) {
                return [
                   
                    'response' => response()->json([
                        'errorCode' => 10003,
                        'message' => 'Order already exists'
                    ]),
                ];
            }
        }

        return $next($request);
    }
}
