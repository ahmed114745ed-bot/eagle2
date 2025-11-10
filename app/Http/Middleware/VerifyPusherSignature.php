<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPusherSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        info('VerifyPusherSignature');

        $signature = $request->header('X-Pusher-Signature');
        $expectedSignature = hash_hmac(
            'sha256',
            $request->getContent(),
            config('broadcasting.connections.pusher.secret')
        );

        if (!hash_equals($expectedSignature, $signature)) {
            info('Pusher webhook signature mismatch.');
            return response('Invalid signature', 403);
        }

        return $next($request);
    }
}
