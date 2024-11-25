<?php

namespace App\Http\Controllers;

use App\Helpers\UserCommon;
use App\Models\Coin;
use App\Models\CoinLog;
use App\Models\User;
use App\Services\StripeService;
use Database\Seeders\config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        ]);

        try {
            $apiKey = config('stripe.test_secret_key');


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
        Log::info(json_encode($request->all()));
        $apiKey = config('stripe.test_secret_key');

        Stripe::setApiKey($apiKey);

        // Retrieve the request's body and Stripe signature header
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Your Stripe webhook secret, which you get from the Stripe dashboard
        $endpointSecret = config('stripe.webhook_secret'); // Set this in your .env file

        try {
            // Verify the webhook signature to ensure it's coming from Stripe
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            // Handle the event types
            switch ($event->type) {
                case 'checkout.session.completed':
                    // Payment successful
                    $session = $event->data->object; // Contains session details

                    $userId = $session->metadata->user_id;
                    $orderId = $session->metadata->order_id;
                    $product_id = $session->metadata->product_id;

                    $this->makePayment($orderId, $product_id, $userId);
                    // Handle successful payment here (e.g., update database)
                    // You can access $session->id, $session->payment_status, etc.
                    \Log::info("Payment successful for session: {$session->id}");

                    break;

                case 'payment_intent.succeeded':


                    break;
                case 'payment_intent.failed':
                    // Payment failed
                    $paymentIntent = $event->data->object; // Contains payment intent details
                    // Handle failed payment here (e.g., notify user)
                    \Log::info("Payment failed for payment intent: {$paymentIntent->id}");
                    break;

                default:
                    // Handle other events if needed
                    \Log::info("Unhandled event type: {$event->type}");
                    break;
            }

            // Return a 200 response to Stripe to acknowledge the webhook
            return response('Webhook Handled', 200);
        } catch (SignatureVerificationException $e) {
            // Invalid signature from Stripe
            \Log::error("Invalid webhook signature: {$e->getMessage()}");
            return response('Invalid Signature', 400);
        } catch (\Exception $e) {
            // General error handling
            \Log::error("Webhook error: {$e->getMessage()}");
            return response('Webhook Error: ' . $e->getMessage(), 500);
        }
    }

    public function makePayment($orderId, mixed $productId, int|string|null $userId): false | CoinLog
    {
        if ($userId === null) return false;
        $item  = CoinLog::where("trx", $orderId)->first();
        $coins = Coin::find($productId);

        $data = false;

        if (!$item && $coins) {
            $user = User::find($userId);

            $user->di += $coins->coin;
            $user->save();

            $paid_usd = UserCommon::specialTransfer($coins?->coin);
            $data = CoinLog::create([
                "paid_usd" => $paid_usd,
                "obtained_coins" => $coins?->coin,
                "user_id"        => $userId,
                'method'         => "google_pay",
                'donor_id'       => 0,
                'donor_type'     => 0,
                'status'         => 1,
                'trx'            => $orderId,
            ]);
        }
        return $data;
    }
}
