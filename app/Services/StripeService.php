<?php

namespace App\Services;

use App\Models\Setting;
use Database\Seeders\config;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

class StripeService {


    public function pay($apiKey, $request){

        Stripe::setApiKey($apiKey);
        try {

           
            $stripe_test_cancel_url = Setting::where('key', 'stripe_cancel_url')->first();
            $stripe_test_success_url = Setting::where('key', 'stripe_success_url')->first();
            $stripe_currency = Setting::where('key', 'stripe_currency')->first();
    
            // Create a checkout session
            $session = StripeCheckoutSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' =>   'usd',
                        'product_data' => [
                            'name' => $request->product_name,
                        ],
                        'unit_amount' => $request->amount, 

                    ],
                    'quantity' => $request->quantity ?? 1,
                ]],
                'mode' => 'payment',
                'success_url' => $stripe_test_success_url?->value,
                'cancel_url' => $stripe_test_cancel_url?->value,
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
