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
        Log::info('Codapay Callback Middleware Triggered', [
            'payload' => $request->all(),
        ]);

        $required = ['TxnId', 'OrderId', 'TotalPrice', 'Checksum'];
        foreach ($required as $field) {
            if (!$request->has($field)) {
                Log::warning("Codapay callback missing field: {$field}");
                return response()->json(['error' => "Missing required field: {$field}"], 400);
            }
        }

        $txnId     = $request->get('TxnId');
        $orderId   = $request->get('OrderId');
        $amount    = $request->get('TotalPrice');
        $checksum  = $request->get('Checksum');

        $secretKey = config('codapay.api_key');

        $computedChecksum = md5($secretKey . $txnId . $orderId . $amount);

        if ($computedChecksum !== $checksum) {
            Log::warning('Codapay checksum verification failed', [
                'received' => $checksum,
                'expected' => $computedChecksum,
            ]);
            return response()->json(['error' => 'Invalid checksum'], 403);
        }

        Log::info('Codapay checksum verified successfully', [
            'TxnId' => $txnId,
            'OrderId' => $orderId,
        ]);

        return $next($request);
    }
}
