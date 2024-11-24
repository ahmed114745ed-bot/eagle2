<?php

namespace App\Services;

use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

class StripeService {


    public function pay($apiKey, array $input){

        Stripe::setApiKey($apiKey);
        try {
            // Create a checkout session
            $session = StripeCheckoutSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $input['currency'] ?? 'usd',
                        'product_data' => [
                            'name' => $input['product_name'],
                        ],
                        'unit_amount' => $input['amount'], // amount in cents
                    ],
                    'quantity' => $input['quantity'] ?? 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'https://www.google.com',
                'cancel_url' => 'https://www.google.com',
            ]);

            // Return the payment link
            return $session->url;
        } catch (\Exception $e) {
            // Handle exceptions and rethrow for the caller
            throw new \Exception('Error generating payment link: ' . $e->getMessage());
        }
    }
}
