<?php

namespace App\Http\Controllers;

use App\Helpers\UserCommon;
use App\Models\Coin;
use App\Models\CoinLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\StripeService;
use Database\Seeders\config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function __construct(public StripeService $stripeService) {}
    public function pay(Request $request)
    {


        $request->validate([
            'product_name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
            'coin_id' => 'required|numeric'
        ]);
        try {

            $apiKey = config('stripe.test_secret_key');

            $request->user_id = auth()->id();

            $coin = Coin::query()->find($request->coin_id);

            $trx = rand (111111111111111111,999999999999999999);

            $order = CoinLog::query()->create(
                [
                    'paid_usd' => $coin->usd,
                    'user_id' => auth()->id(),
                    'obtained_coins' => $coin->coin,
                    'method' => 'card',
                    'trx' => $trx,
                    'status' => 0
                ]
            );

            $request->order_id = $order->id;

            $link = $this->stripeService->pay($apiKey, $request);


            return response()->json([
                'message' => 'Link generated successfully',
                'link' => $link
            ]);
        } catch (\Exception $e) {
            // Handle any other errors (e.g., API issues, server errors)
            return response()->json([
                'message' => 'Error generating payment link: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function handleWebhook(Request $request)
    {
        info('welcome to webhook');
        $stripe_test_secret_key = Setting::where('key', 'stripe_test_secret_key')->first();
        $stripe_webhook_secret = Setting::where('key', 'stripe_webhook_secret')->first();

        $apiKey = $stripe_test_secret_key;

        Stripe::setApiKey($apiKey);

        // Retrieve the request's body and Stripe signature header
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        // Your Stripe webhook secret, which you get from the Stripe dashboard
        $endpointSecret = $stripe_webhook_secret?->value; // Set this in your .env file
        Log::info('strip callback called '. $apiKey . ' '. $stripe_webhook_secret);
//        try {
            // Verify the webhook signature to ensure it's coming from Stripe
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            info($event->type);

            // Handle the event types
            switch ($event->type) {
                case 'checkout.session.completed':
                    // Payment successful
                    $session = $event->data->object; // Contains session details

                    $userId = $session->metadata->user_id;
                    $orderId = $session->metadata->order_id;
                    info($orderId);

                    $this->makePayment($orderId, $userId);
                    info('completed');
                    // Handle successful payment here (e.g., update database)
                    // You can access $session->id, $session->payment_status, etc.
                    break;

                case 'payment_intent.succeeded':

                    $session = $event->data->object; // Contains session details

                    $userId = $session->metadata->user_id;
                    $orderId = $session->metadata->order_id;

                    $this->makePayment($orderId, $userId);
                    info('succeeded');
                    // Handle successful payment here (e.g., update database)
                    // You can access $session->id, $session->payment_status, etc.

                    break;
                case 'payment_intent.failed':
                    // Payment failed
                    info('failed');
                    $paymentIntent = $event->data->object; // Contains payment intent details
                    // Handle failed payment here (e.g., notify user)
                    break;

                default:
                    // Handle other events if needed
                    break;
            }

            info('Webhook Handled 200');
            // Return a 200 response to Stripe to acknowledge the webhook
            return response('Webhook Handled', 200);
//        } catch (SignatureVerificationException $e) {
//            // Invalid signature from Stripe
//            \Log::error("Invalid webhook signature: {$e->getMessage()}");
//            return response('Invalid Signature', 400);
//        } catch (\Exception $e) {
//            // General error handling
//            \Log::error("Webhook error: {$e->getMessage()}");
//            return response('Webhook Error: ' . $e->getMessage(), 500);
//        }
    }

    public function makePayment($orderId, int|string|null $userId)
    {
        if ($userId === null) return false;

        $item  = CoinLog::where("id", $orderId)->first();

        info($item);

        if($item->status == 1){
            return false;
        }

        $item->status = 1;

        $item->save();

        $user = User::find($userId);

        $user->di += $item->obtained_coins;

        $user->save();
    }
}
