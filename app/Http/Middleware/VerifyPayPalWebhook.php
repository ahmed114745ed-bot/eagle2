<?php

namespace App\Http\Middleware;

use App\Helpers\LogHelper;
use App\Services\PayPalService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyPayPalWebhook extends PayPalService
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $headers = $request->headers;


        $payload = file_get_contents('php://input');

        $response = Http::withToken(app(PayPalService::class)->getAccessToken())
            ->post(config('paypal.base_url') . '/v1/notifications/verify-webhook-signature', [
                'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'],
                'cert_url'          => $headers['PAYPAL-CERT-URL'],
                'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'],
                'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'],
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'],
                'webhook_id'        => config('paypal.webhook_id'), // must match dashboard
                'webhook_event'     => $payload, // full JSON body
            ]);

        LogHelper::info('this is middleware ', [
            'headers' => $headers,
            'base_url' => config('paypal.base_url'),
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);
        if ($response->json('verification_status') !== 'SUCCESS') {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        return $next($request);
    }
}
