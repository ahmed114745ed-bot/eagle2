<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Coin;
use App\Models\CoinLog;
use App\Models\User;
use App\Helpers\Common;
use App\Traits\User\PaymentTrait;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
// use Google_Client;
use GuzzleHttp\Client;

class GooglePaymentController extends Controller
{

    use PaymentTrait;
    public function purchased(Request $request)
    {
        // Set the path to your service account JSON file
        $serviceAccountFile = public_path('r-star-shop-23ba690c77fe.json');
        // Set the scopes
        $scopes = ['https://www.googleapis.com/auth/sqlservice.admin'];

        // Create a new Google API client
        $client =  new \Google_Client();
        $client->setApplicationName("r-star-shop");
        $client->setAuthConfig($serviceAccountFile);
        $client->addScope($scopes);

        // Fetch credentials
        $credentials = $client->fetchAccessTokenWithAssertion();

        // dd($credentials);
        // Check if the access token is present
        if (isset($credentials['access_token'])) {
            $accessToken = "Bearer " . $credentials['access_token'];
            // first json_encode the access token before sending it to $client->setAccessToken();
            $json_encoded_access_token = json_encode([
                'access_token' => $accessToken,
                'created' => $credentials['created'],  // make up values for these.. otherwise the client thinks the token has expired..
                'expires_in' => $credentials['expires_in'] // made up a value in the future...
            ]);

            // and then set it
            // $client->setAccessToken($json_encoded_access_token);

            // Set up a new Google API client for the Android Publisher service
            $androidClient =  new \Google_Client();
            $androidClient->setApplicationName("YourAppName");
            $androidClient->setAuthConfig($serviceAccountFile);
            $androidClient->setAccessToken($credentials);

            // Check if the access token is expired and refresh it if necessary
            if ($androidClient->isAccessTokenExpired()) {
                $androidClient->fetchAccessTokenWithRefreshToken($androidClient->getRefreshToken());
            }

            // Set up the Android Publisher service
            $packageName = "com.tikkchat.app";
            $androidService = new \Google_Service_AndroidPublisher($androidClient);

            // This is a simplified example. Ensure proper error handling and security in your implementation.
            $response = $androidService->purchases_products->get($packageName, $request->productId, $request->purchaseToken);

            return $response;
        } else {
            // Handle the case when the access token is not present
            return response()->json(['error' => 'Access token not found'], 401);
        }
    }


    public function purchasedFour(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $url = config('app.payment_url') . '/api/google-pay';

        try {
            $productId = $request->productId;
            $response  = $client->post($url, [
                'json' => [
                    'purchaseToken' => $request->purchaseToken,
                    'productId'     => $productId,
                    'serverKey'     => config('app.node_server_name') // this required
                ],
            ]);
            $body = $response->getBody()->getContents();

            $data = json_decode($body);

            $bodyData = @$data->data ?? null;
            if (@$data->valid && $bodyData) {
                $orderId = $bodyData->orderId;

                $userId = Auth::id();

                $data = $this->makePayment($orderId, $productId, $userId, type: "google_pay");

                if ($data === false) {
                    return Common::apiResponse(0, 'تمت العمليه من قبل!', 402);
                } else {
                    return Common::apiResponse(1, 'تم الاضافه بنجاح', $data, 200);
                }
            }
        } catch (GuzzleException $e) {
            return Common::apiResponse(0, 'هناك مشكله حاول مره اخرى!', 402);
        }
        return Common::apiResponse(0, 'هناك مشكله حاول مره اخرى!', 402);

    }

}
