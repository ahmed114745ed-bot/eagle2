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
        Log::info(json_encode($request->all()));
        Log::info('mohamed-gamal');

        $apiKey = config('stripe.test_secret_key');

        Stripe::setApiKey($apiKey);

        // Retrieve the request's body and Stripe signature header
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        // Your Stripe webhook secret, which you get from the Stripe dashboard
        $endpointSecret = 'whsec_2PTszrAQTltl0FksfIytAfSyQMx3dQqq'; // Set this in your .env file

        try {
            // Verify the webhook signature to ensure it's coming from Stripe
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);


            $session = $event->data->object; // Contains session details
            Log::info(json_encode(['meta_data' =>$session->metadata]));

            // Handle the event types
            switch ($event->type) {
                case 'checkout.session.completed':
                    // Payment successful
                    $session = $event->data->object; // Contains session details

                    $userId = $session->metadata->user_id;
                    $orderId = $session->metadata->order_id;
                    Log::info(json_encode(['user_id' =>$userId, 'order_id' => $orderId]));

                    $this->makePayment($orderId, $userId);
                    // Handle successful payment here (e.g., update database)
                    // You can access $session->id, $session->payment_status, etc.
                    \Log::info("Payment successful for session: {$session->id}");
                    Log::info('completed');
                    break;

                case 'payment_intent.succeeded':

                    $session = $event->data->object; // Contains session details

                    $userId = $session->metadata->user_id;
                    $orderId = $session->metadata->order_id;
                    Log::info(json_encode(['user_id' =>$userId, 'order_id' => $orderId]));

                    $this->makePayment($orderId, $userId);
                    // Handle successful payment here (e.g., update database)
                    // You can access $session->id, $session->payment_status, etc.
                    Log::info('succeeded');
                    \Log::info("Payment successful for session: {$session->id}");

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

    public function makePayment($orderId, int|string|null $userId)
    {
        if ($userId === null) return false;

        $item  = CoinLog::where("id", $orderId)->first();

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
