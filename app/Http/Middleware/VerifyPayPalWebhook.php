<?php

namespace App\Http\Middleware;

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

        $verificationData = [
            'auth_algo'         => $headers->get('paypal-auth-algo'),
            'cert_url'          => $headers->get('paypal-cert-url'),
            'transmission_id'   => $headers->get('paypal-transmission-id'),
            'transmission_sig'  => $headers->get('paypal-transmission-sig'),
            'transmission_time' => $headers->get('paypal-transmission-time'),
            'webhook_id'        => config('paypal.webhook_id'),
            'webhook_event'     => $request->all(),
        ];

        $accessToken = (new PayPalService())->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post(config('paypal.base_url') . '/v1/notifications/verify-webhook-signature', $verificationData);

        if ($response->json('verification_status') !== 'SUCCESS') {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        return $next($request);
    }
}
