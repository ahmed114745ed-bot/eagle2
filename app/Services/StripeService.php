<?php

namespace App\Services;

use App\Models\CoinLog;
use App\Models\Setting;
use Database\Seeders\config;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

class StripeService {

    public function pay(array $settings, array $data): string
    {
        Stripe::setApiKey($settings['secret_key']);

        try {
            $amountInCents = intval($data['amount'] * 100);

            $session = StripeCheckoutSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $settings['currency'] ?? 'usd',
                        'product_data' => [
                            'name' => $data['product_name'],
                        ],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => $data['quantity'] ?? 1,
                ]],
                'mode' => 'payment',
                'success_url' => $settings['success_url'] . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $settings['cancel_url'],
                'metadata' => [
                    'user_id'  => $data['user_id'],
                    'order_id' => $data['order_id'],
                ],
            ]);

            CoinLog::where('id', $data['order_id'])
                ->update(['trx' => $session->id]);

            Log::info("Stripe session created", [
                'session_id' => $session->id,
                'order_id'   => $data['order_id'],
            ]);

            return $session->url;

        } catch (\Exception $e) {
            Log::error("Stripe payment error", [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);

            throw new \Exception('Error generating payment link: ' . $e->getMessage());
        }
    }
}
    

