<?php

namespace App\Http\Middleware;

use App\Helpers\SignatureHelper;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGameSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->get('signature');
        $signatureNonce = $request->get('signature_nonce');
        $timestamp = $request->get('timestamp');
        $appKey = config('services.baishun.app_key');
        $currentTimestamp = Carbon::now()->timestamp;

        // \Log::info('signature is ' . json_encode($signature) . PHP_EOL . ' signatureNonce is '. json_encode($signatureNonce) . PHP_EOL .' timestamp is '. json_encode($timestamp) . PHP_EOL .
        // 'is !$signature || !$signatureNonce || !$timestamp ' . json_encode(!$signature || !$signatureNonce || !$timestamp) . PHP_EOL .
        //     'is abs($currentTimestamp - $timestamp) > 15 ' . json_encode(abs($currentTimestamp - $timestamp) > 15)  . PHP_EOL .

        //     'is !SignatureHelper::verifySignature($signature, $signatureNonce, $appKey, $timestamp) ' . json_encode(!SignatureHelper::verifySignature($signature, $signatureNonce, $appKey, $timestamp))
        // );

        if (!$signature || !$signatureNonce || !$timestamp) {
            return response()->json(['error' => 'Missing signature parameters'], 400);
        }


        if (abs($currentTimestamp - $timestamp) > 15) {
            return response()->json(['error' => 'Signature timestamp is invalid'], 400);
        }

        if (!SignatureHelper::verifySignature($signature, $signatureNonce, $appKey, $timestamp)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
