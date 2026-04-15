<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyUtdPayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('utd.webhook_secret');

        // لو مفيش secret متظبط → نعدي (عشان مش نكسر حاجة قبل ما يتظبط)
        if (empty($secret)) {
            return $next($request);
        }

        $signature = $request->header('X-UTD-Signature');
        $timestamp = $request->header('X-UTD-Timestamp');

        if (!$signature || !$timestamp) {
            return response()->json(['error' => 'Missing signature headers'], 401);
        }

        // حماية من replay attacks — الـ timestamp لازم يكون في آخر 5 دقايق
        if (abs(time() - (int) $timestamp) > 300) {
            return response()->json(['error' => 'Signature timestamp expired'], 401);
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
