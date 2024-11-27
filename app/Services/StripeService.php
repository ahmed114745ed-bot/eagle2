<?php

namespace App\Services;

use Database\Seeders\config;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

class StripeService {


    public function pay($apiKey, $request){

        Stripe::setApiKey($apiKey);
        try {
            // Create a checkout session
            $session = StripeCheckoutSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => config('stripe.currency') ?? 'usd',
                        'product_data' => [
                            'name' => $request->product_name,
                        ],
                        'unit_amount' => $request->amount, // amount in cents
                    ],
                    'quantity' => $request->quantity ?? 1,
                ]],
                'mode' => 'payment',
                'success_url' => config('stripe.success_url'),
                'cancel_url' => config('stripe.cancel_url'),
                'metadata' => [
                    'user_id' => $request->user_id,
                    'order_id' => $request->order_id,
                ]
            ]);
            // Return the payment link
            return $session->url;
        }
        catch (\Exception $e) {
            // Handle exceptions and rethrow for the caller
            throw new \Exception('Error generating payment link: ' . $e->getMessage());
        }
    }
}
