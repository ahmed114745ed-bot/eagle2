<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyCodapayWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Codapay Callback Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        $providedSignature = $request->header('X-Codapay-Signature');
        $secretKey = config('codapay.api_key');

        if ($providedSignature) {
            $expectedSignature = hash_hmac('sha256', json_encode($request->all()), $secretKey);

            if (!hash_equals($expectedSignature, $providedSignature)) {
                Log::warning('Codapay Signature Verification Failed');
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        } else {
            Log::warning('Codapay callback missing signature header');
        }

        return $next($request);
    }
}
