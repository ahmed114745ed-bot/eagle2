<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyUtdPayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        info('utd pay webhook middleware');
        $secret = config('utd.webhook_secret');

        if ($secret) {
            info('', [$secret]);
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $signature = $request->header('X-UTD-Signature');
        $timestamp = $request->header('X-UTD-Timestamp');

        if (!$signature || !$timestamp) {
            info('Missing signature headers');
            return response()->json(['error' => 'Missing signature headers'], 401);
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return response()->json(['error' => 'Signature timestamp expired'], 401);
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            info('Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
