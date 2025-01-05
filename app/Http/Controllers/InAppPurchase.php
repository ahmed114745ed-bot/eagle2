<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\User;
use App\Helpers\Common;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Google\Service\AndroidPublisher;
use App\Http\Controllers\Web\PaymentController;
use App\Models\CoinLog;
use Google_Client;
use Imdhemy\GooglePlay\ClientFactory;
use Imdhemy\GooglePlay\Products\ProductPurchase;
use Imdhemy\Purchases\Product;
use Tests\Products\ProductPurchaseTest;


class InAppPurchase extends Controller
{
    protected $client;
     public function __construct()
     {
         $this->client = new \Google_Client();
//         $this->client = new Google_Client();
         $credentialPath = public_path('google_payment.json');
         if (!file_exists($credentialPath)) {
             throw new \Exception('Google payment credential file does not exist at path: ' . $credentialPath);
         }
         $this->client->setApplicationName('Tik chat');
         $this->client->setAuthConfig($credentialPath);
         $this->client->setScopes([AndroidPublisher::ANDROIDPUBLISHER]);
// //        $this->client->setSubject('google-pay-new-key@r-star-shop.iam.gserviceaccount.com'); // Replace with your service account email

// //        $this->client->setScopes(['https://www.googleapis.com/auth/androidpublisher']);
     }


     public function googlePay($token, $productId, )
     {
         $client = ClientFactory::create([ClientFactory::SCOPE_ANDROID_PUBLISHER]);
         $product = new Product();
         try {
             $response = $product->googlePlay($client)->packageName('com.tikkchat.app')->token($token)->id($productId)->get();
             dd($response, 'this');
         } catch (GuzzleException $e) {
             dd($e->getMessage(), 'this');
         }

                 $subscription->verifyReceipt($client);
     }


    function xorDecrypt($input, $key)
    {
        $keyBytes = utf8_encode($key);
        $inputBytes = base64_decode($input);
        $decryptedData = '';

        for ($i = 0; $i < strlen($inputBytes); $i++) {
            $decryptedData .= chr(ord($inputBytes[$i]) ^ ord($keyBytes[$i % strlen($keyBytes)]));
        }

        return $decryptedData;
    }

    public function verifyToken($message, $key)
    {
        $decryptedData = $this->xorDecrypt($message, $key);

        if (json_decode($decryptedData, true)) {
            $data = json_decode($decryptedData, true);
            $merchantId = $data['merchantInfo']['merchantId'];
            $merchantName = $data['merchantInfo']['merchantName'];

            $env_merchantId = config('view.merchant_id');
            $env_merchantName = config('view.merchant_name');

            if ($env_merchantId !== $merchantId  ||  $env_merchantName !== $merchantName) {
                return [1, $data];
            }

            return [2, $data];
            //return successful message
        } else {
            return [0, null];
        }
    }


    public function tokenEncode($message, $key)
    {
        $encryptedData = $this->xorDecrypt($message, $key);
        $jsonData = json_encode($encryptedData);

        if ($jsonData !== false) {
            $data = base64_encode(serialize($jsonData));
            $data = unserialize(base64_decode($data));
            $merchantId = $data['merchantInfo']['merchantId'];
            $merchantName = $data['merchantInfo']['merchantName'];

            $env_merchantId = config('view.merchant_id');
            $env_merchantName = config('view.merchant_name');

            if ($env_merchantId !== $merchantId || $env_merchantName !== $merchantName) {
                return [1, $data];
            }

            return [2, $data];
            //return successful message
        } else {
            return [0, null];
        }
    }

    public function pay(Request $request)
    {
        $path = public_path('google_payment.json');
        putenv(sprintf("GOOGLE_APPLICATION_CREDENTIALS=%s", $path));
        $user = $request->user();

        $message = $request->message;
        $userId      = $user->id;
        $key     = $userId .'-'.config('view.decrypt_key');
        $type    = $request->type ?? 'google_pay';

        [$value, $data] = $this->verifyToken($message, $key);

        switch ($value) {
            case 0:
                return response()->json([
                    'status' => 404,
                    'message' => 'something went wrong',
                ],404);
            case 1:
                return Common::apiResponse(0, 'fail', null, 400);
            case 2:

                $trx = $data['processInfo']['token'];

                if (!$trx || $this->checkPayment($userId, $trx)) return Common::apiResponse(false, __('Not Allowed'));
                $coins = Coin::where('id', $data['processInfo']['item_id'])->first();
                if (!$coins) return Common::apiResponse(false, __('item id not founded'));
                //increment user di = coins from coins table coins column
                $user->di += $coins->coin;
                $user->save();

                $this->coinLog($coins->coin, $type, $userId, trx: $trx);
                //save user change
                return Common::apiResponse(true, 'success', null, 200);
            default:
                return Common::apiResponse(0, 'un handling exception', null, 400);
        }
    }

    public function checkPayment($userId, string $trx ) : bool
    {
        return CoinLog::query()->where('user_id', $userId)->where('trx', $trx)->exists();
    }
    public function coinLog(int $coins, $type, $user_id, string $trx = null) : void
    {
        $coinLog = new CoinLog();
        $coinLog->obtained_coins = $coins;
        $coinLog->user_id = $user_id;
        $coinLog->method = $type;

        $coinLog->donor_id = null;
        $coinLog->donor_type =  null;
        $coinLog->status = 1;
        $coinLog->trx = $trx;
        $coinLog->save();
    }

    public function verifyGooglePayPayment($productId, $purchaseToken)
    {
        $service = new AndroidPublisher($this->client);


//        try {
            $response = $service->purchases_products->get(
                'com.tikkchat.app',
                $productId,
                $purchaseToken
            );


            // Verify the purchase details
            if ($response->getPurchaseState() == 0) {
                // Purchase is valid
                // Acknowledge the purchase if necessary
                // Grant the product to the user in your system
                return response()->json();
            } else {
                // Handle invalid purchase
            }



        /*} catch (\Google\Service\Exception $e) {
            // Log or return the detailed error message for debugging
            error_log($e->getMessage());
            return false;
        }*/
    }

    public function verifyGooglePay(Request $request)
    {
        $productId = $request->input('productId') ?? 1;
        $purchaseToken = $request->input('purchaseToken') ?? 'this';


        $googlePlayVerifier = $this;
        $isVerified = $googlePlayVerifier->verifyGooglePayPayment($productId, $purchaseToken);

        if ($isVerified) {
            return response()->json(['status' => 'success', 'message' => 'Payment verified.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid payment.']);
        }
    }

    public function googleCoinsPay(Request $request)
    {
        return Common::apiResponse(false, 'حاول مره اخري', null, 422);
        $user = $request->user();
        $coins = Coin::find($request->coin_id);
        if(!$coins)return Common::apiResponse(0, __('api_responses.missing_params'), null, 422);
        if(!$request->order_id)return Common::apiResponse(0, __('api_responses.missing_params'), null, 422);
        $user->di += $coins->coin;
        $user->save();
        $data=CoinLog::create([
            "obtained_coins"=> $coins->coin,
            "user_id"=>$user->id,
            'method'=>"google_pay",
            'donor_id'=>0,
            'donor_type'=>0,
            'status'=>1,
            'trx'=>$request->order_id,
        ]);
        return Common::apiResponse(1, 'تم الاضافه بنجاح', $data, 200);
    }
}
