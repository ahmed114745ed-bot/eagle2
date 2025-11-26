<?php

namespace App\Http\Controllers;

use App\Enums\UserCoinLogType;
use App\Helpers\LogHelper;
use App\Helpers\UserCoinLogHelper;
use App\Helpers\UserCommon;
use App\Models\Coin;
use App\Models\CoinLog;
use App\Models\Setting;
use App\Models\ShippingAgency;
use App\Models\User;
use App\Services\StripeService;
use App\Traits\User\PaymentTrait;
use Database\Seeders\config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Achievement\Http\Services\UserAchievementService;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    use PaymentTrait;

    private const SUCCESS_STATUSES = ['succeeded', 'paid'];

    public function __construct(public StripeService $stripeService) {}
    public function pay(Request $request)
    {
        $request->validate([
            'quantity'     => 'required|integer|min:1',
            'coin_id'      => 'required|numeric'
        ]);
    
        try {
            $stripe_test_secret_key = Setting::where('key', 'stripe_test_secret_key')->first();
            $apiKey = $stripe_test_secret_key?->value;
    
            $coin = Coin::findOrFail($request->coin_id);
            $trx  = rand(111111111111111111, 999999999999999999);
    
            // إنشاء الطلب في النظام
            $order = CoinLog::query()->create([
                'coin_id'        => $coin->id,
                'paid_usd'       => $coin->usd,
                'user_id'        => auth()->id(),
                'obtained_coins' => $coin->coin,
                'method'         => 'card',
                'trx'            => $trx,
                'status'         => 0
            ]);
    
            $paymentRequest = new \Illuminate\Http\Request([
                'product_name' => $coin->coin,
                'amount'       => $coin->usd,
                'quantity'     => $request->quantity,
                'order_id'     => $order->id,
            ]);
    
            $link = $this->stripeService->pay($apiKey, $paymentRequest);
    
            return response()->json([
                'message' => 'Link generated successfully',
                'link'    => $link
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating payment link: ' . $e->getMessage(),
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function handleWebhook(Request $request)
    {


        $apiKey         = Setting::where('key', 'stripe_test_secret_key')->value('value');
        $endpointSecret = Setting::where('key', 'stripe_webhook_secret')->value('value');

        Stripe::setApiKey($apiKey);

        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            [$orderId, $trxId, $status] = $this->extractStripeEventData($event);

            if (!$trxId && !$orderId) {
                return response('Ignored: no IDs', 200);
            }

            if (!in_array($status, self::SUCCESS_STATUSES)) {
                return response('Ignored: not successful', 200);
            }
            
            if (in_array($status, self::SUCCESS_STATUSES)) {
                 $this->markCoinLogAsPaid($orderId, $trxId);
            }
            return response('Webhook Handled', 200);

        } catch (SignatureVerificationException $e) {
            return response('Invalid Signature', 400);

        } catch (\Exception $e) {
            return response('Webhook Error: ' . $e->getMessage(), 500);
        }
    }

    private function extractStripeEventData(object $event): array
    {
        $orderId = null;
        $trxId   = null;
        $status  = null;

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $orderId = $session->metadata->order_id ?? null;
                $trxId   = $session->payment_intent;
                $status  = $session->payment_status ?? null;
                break;

            case 'payment_intent.succeeded':
                $pi     = $event->data->object;
                $trxId  = $pi->id;
                $status = $pi->status;
                break;

            case 'charge.succeeded':
                $charge = $event->data->object;
                $trxId  = $charge->payment_intent ?? $charge->id;
                $status = $charge->status;
                break;

            case 'payment_intent.payment_failed':
            case 'payment_intent.canceled':
                $pi     = $event->data->object;
                $trxId  = $pi->id;
                $status = $pi->status;
                break;

            default:
        }

        return [$orderId, $trxId, $status];
    }

    private function markCoinLogAsPaid(?string $orderId, ?string $trxId)
    {
        $coinLog = $this->findCoinLog($orderId, $trxId);
        if (!$coinLog) return;
    
        if ($this->isAlreadyProcessed($coinLog)) return;
    
        $this->updateCoinLogAsPaid($coinLog, $trxId);
        $this->resolveCoinLogOwner($coinLog);

        return   $this->finalizeResponse($coinLog);
    }

    private function resolveCoinLogOwner(CoinLog $coinLog)
    {
        $owner = $coinLog->owner;

        if ($owner instanceof User) {
            return $this->processUserPayment($owner, $coinLog);
        }

        if ($owner instanceof ShippingAgency) {
            return $this->processAgencyPayment($owner, $coinLog);
        }

        return null;
    }

    private function findCoinLog(?string $orderId, ?string $trxId): ?CoinLog
    {
        $coinLog = CoinLog::find($orderId);
    
        if (!$coinLog) {
            LogHelper::info("Stripe Webhook: No CoinLog found", [
                'orderId' => $orderId,
                'trxId'   => $trxId,
            ]);
        }
    
        return $coinLog;
    }
    
 
    private function isAlreadyProcessed(CoinLog $coinLog): bool
    {
        if ($coinLog->status == 1) {
            return true;
        }
        return false;
    }
    

    private function updateCoinLogAsPaid(CoinLog $coinLog, ?string $trxId): void
    {
        $coinLog->update([
            'trx'     => $trxId,
            'status' => true,
        ]);
    
    }
    
 
    private function handleMissingUser(CoinLog $coinLog)
    {
        LogHelper::info("Stripe Webhook: No user found for CoinLog", [
            'coinLogId' => $coinLog->id,
        ]);
        return response()->json([
            'status'  => false,
            'trx'     => $coinLog->trx,
            'message' => 'Transaction failed. User not found.',
        ]);
    }
    

    private function processUserPayment(User $user, CoinLog $coinLog): void
    {
        $amountBefore = $user->di;
        $user->increment('di', $coinLog->obtained_coins);
    
        UserCoinLogHelper::logByType(
            $user->id,
            $coinLog->obtained_coins,
            $amountBefore,
            UserCoinLogType::PAYMENT,
            featureType:'stripe'
        );
    
        UserCommon::addChargeLevel($user->id, $coinLog->obtained_coins);
    
        (new UserAchievementService())->insertCharging($user, $coinLog->obtained_coins);
    
    }

    private function processAgencyPayment(ShippingAgency $agency, CoinLog $coinLog): void
    {
        $agency->increment('coins', $coinLog->obtained_coins);
    }
    
    private function finalizeResponse(CoinLog $coinLog)
    {
        LogHelper::info("Stripe Webhook: Transaction {$coinLog->trx} completed successfully", [
            'coinLogId' => $coinLog->id,
        ]);
        return response()->json([
            'status'  => true,
            'trx'     => $coinLog->trx,
            'message' => 'Transaction completed successfully.',
        ]);
    }



    
    
    
    
    public function success(Request $request)
    {

        try {
            $orderId = $request->get('orderId');

            $coin = CoinLog::find($orderId);
            return response()->json([
                'status'  => true,
                'trx'     => $coin->trx,
                'message' => 'Transaction completed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Payment failed.',], 500);
        }
    }

    public function cancel(Request $request)
    {

        return response()->json([
            'status'  => false,
            'trx'     => '',
            'message' => 'Transaction cancelled.',
        ], 500);
    }

}
