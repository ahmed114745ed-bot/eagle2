<?php

namespace App\Http\Controllers\Api\V1;


use App\Events\DeviceTokenSent;
use App\helper\AccountHelper;
use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Http\Services\WhatsappOtp;
use App\Models\DevicesTokenHistory;
use App\Models\User;
use App\Tik\Services\AuthService;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Google_Client;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\SwitchAccount\Http\Services\SwitchAccountServices;

class AuthController extends Controller
{

    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;

        if (!$phone || !$request->code) {
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $isValid  = $whatsappOtpService->isValidate($phone, $request->code);

        if (!$isValid) {
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $whatsappOtpService->resetCodes($phone);
        
        // Check device account limit BEFORE registration
        $deviceToken = $request->input('device_token');
        
        if (!empty($deviceToken)) {
            try {
                $register_account = (int)(Common::getSettingValue('register_account') ?? 3);
                
                $record = DevicesTokenHistory::where('device_token', $deviceToken)->first();
                
                if ($record && $record->count >= $register_account) {
                    \Log::warning('Device account limit exceeded', [
                        'device_token' => $deviceToken,
                        'current_count' => $record->count,
                        'limit' => $register_account
                    ]);
                    return Common::apiResponse(false, __('max_accounts_reached'), [], 422);
                }
            } catch (\Exception $e) {
                \Log::error('Device token check failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        } else {
            \Log::warning('No device token provided in registration request', ['phone' => $phone]);
        }
        
        try {
            [$user, $token] = $this->authService->registration($request);
        } catch (\Exception $exception) {
            \Log::error('Registration failed', ['error' => $exception->getMessage(), 'phone' => $phone]);
            return Common::apiResponse(0, $exception->getMessage(), null, 422);
        }

        $user->auth_token = $token;
        return Common::apiResponse(
            true,
            __('api_responses.logged'),
            [
                'id'            => $user->id,
                'is_first'      => @(bool)$user->is_points_first,
                'auth_token'    => $user->auth_token
            ]
        );
    }

    public function login(LoginRequest $request)
    {
        $globalKeys = [
            'is_multi' => $request->input('is_multi', false),
            'notification_id' => $request->input('notification_id', null)
        ];

        switch ($request['type']) {
            case 'phone_pass':
                $fields = ['phone' => $request['phone'], 'password' => $request['password'], 'device_token' => $request['device_token'], 'uuid' => $request['uuid']];
                $fields = array_merge($globalKeys, $fields);
                return $this->loginWithPhonePassword($fields);
            case 'google':
                $fields = ['name' => $request->name, 'email' => $request->email, 'google_id' => $request['google_id'], 'device_token' => $request['device_token'], 'id_token' => $request['id_token'], 'image' => $request['google_image'], 'lat' => $request['lat'], 'long' => $request['long'], 'iso' => $request['iso'], 'uuid' => $request['uuid']];
                $fields = array_merge($globalKeys, $fields);
                $response = $this->loginWithGoogle($fields);
                return $response;
            case 'apple':
                $fields = [
                    'name'          => $request->name,
                    'email'         => $request->email,
                    'id_token'      => $request->id_token,
                    'device_token'  => $request->device_token,
                    'lat'           => $request->lat,
                    'long'          => $request->long,
                    'iso'           => $request->iso,
                    'uuid'          => $request->uuid,
                ];
                $fields = array_merge($globalKeys, $fields);
                return $this->loginWithApple($fields);
            case 'huawei':
                $fields = ['name' => $request->name, 'device_token'  => $request->device_token, 'email' => $request->email, 'huawei_id' => $request->huawei_id, 'id_token' => $request->id_token, 'lat' => $request['lat'], 'long' => $request['long'], 'iso' => $request['iso'], 'uuid' => $request['uuid']];
                $fields = array_merge($globalKeys, $fields);
                return $this->loginWithHuawei($fields);

            default:
                return Common::apiResponse(false, 'invalid login method', null, 422);
        }
    }

    protected function loginWithPhonePassword($fields)
    {

        try {
            [$user, $token] = $this->authService->loginWithPassword($fields);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 422);
        }
        if (!$this->canLogin($user)) {
            return Common::apiResponse(false, 'you are blocked', [], 422);
        }
        try {

            if ($user->device_token) {
                event(new DeviceTokenSent($user->id, $user->device_token));
            }
            // event(new DeviceTokenSent($user->id, $user->device_token));
        } catch (\Exception $e) {
        }

        $user->auth_token = $token;
        if ($user->device_token) AccountHelper::linkLoginAccountWithDevice($user->id, $user->device_token);

        return Common::apiResponse(
            true,
            __('api_responses.logged'),
            [
                'id'            => $user->id,
                'is_first'      => (bool) ($user->is_points_first ?? false),  //    @(bool)$user->is_points_first,
                'auth_token'    => $user->auth_token
            ]
        );
    }

    protected function loginWithGoogle($data)
    {
        try {
            $result = $this->authService->loginWithGoogle($data);

            // If the service returned a response directly (e.g. apiResponse for validation errors)
            if (!is_array($result)) {
                return $result;
            }

            [$user, $token, $resource] = $result;

            if (!$this->canLogin($user)) {
                return Common::apiResponse(false, 'you are blocked', [], 422);
            }
            $user->auth_token = $token;
            try {
                if ($user->device_token) {
                    event(new DeviceTokenSent($user->id, $user->device_token));
                }
            } catch (\Throwable $e) {
                // Ignore event errors
            }
            try {
                AccountHelper::linkLoginAccountWithDevice($user->id, $user->device_token);
            } catch (\Throwable $e) {
                // Ignore device link errors
            }
            return Common::apiResponse(
                true,
                __('api_responses.logged'),
                [
                    'id'            => $user->id,
                    'is_first'      => (bool) ($user->is_points_first ?? false),
                    'auth_token'    => $user->auth_token
                ]
            );
        } catch (\Throwable $exception) {
           // \Log::error('Google login exception', ['error' => $exception->getMessage()]);
            return Common::apiResponse(false, __($exception->getMessage()), [], 422);
        }
    }





    // protected function loginWithApple($data)
    // {
    //     $fields = $data;
    //     $unique_id = $data['apple_id'];
    //     if (!$unique_id)  return Common::apiResponse(0, 'missing app id', null, 422);
    //     $teamId = '4WZ4BZDW8K'; // Use the correct environment variable name
    //     $keyId =  'BKD3JLV6HY'; //"PAN9HH2A6X"/*config('apple.apple_key_id')*/; // Use the correct environment variable name
    //     $clientId = 'com.moon.light.app'; //'com.tikkchat.app'; // Use the correct environment variable name
    //     $redirectUri = config('apple.apple_redirect_uri'); // Use the correct environment variable name
    //     $iat = strtotime('now');
    //     $exp = strtotime('+60days');
    //     $keyContent = file_get_contents(storage_path('app/credentials/AuthKey_BKD3JLV6HY.p8'));
    //     // $keyContent = \Storage::get(storage_path('app/credentials/AuthKey_BKD3JLV6HY.p8'));


    //     $token = JWT::encode([
    //         'iss' => $teamId,
    //         'iat' => $iat,
    //         'exp' => $exp,
    //         'aud' => 'https://appleid.apple.com',
    //         'sub' => $clientId,
    //     ], $keyContent, 'ES256', $keyId);

    //     try {
    //         $res = Http::asForm()->post('https://appleid.apple.com/auth/token', [
    //             'grant_type' => 'authorization_code',
    //             'code' => $unique_id,
    //             'redirect_uri' => $redirectUri,
    //             'client_id' => $clientId,
    //             'client_secret' => $token,
    //         ]);
    //     \App\Helpers\LogHelper::info('response apple', $res->json());
    //         if ($res->successful()) {
    //             $claims = explode('.', $res['id_token'])[1];
    //             $data = json_decode(base64_decode($claims), true);
    //         } else {
    //             return      Common::apiResponse(0, 'data not full', null, 422);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'wrong credential.', 'message' => $e->getMessage()], 403);
    //     }

    //     try {
    //         [$user, $token] = $this->authService->loginWithApple($data, $unique_id);
    //     } catch (\Exception $exception) {

    //         return Common::apiResponse(0, $exception->getMessage(), null, 422);
    //     }
    //     // if (!$this->canLogin($user)) {
    //     //     return Common::apiResponse(false, 'you are blocked', [], 408);
    //     // }

    //     $user->auth_token = $token;
    //     event(new DeviceTokenSent($user->id, $user->device_token));
    //     AccountHelper::linkLoginAccountWithDevice($user->id, $user->device_token);

    //     return Common::apiResponse(true, '', new MyDataResource($user), 200);
    // }

    protected function loginWithApple($data)
    {
        if (empty($data['id_token'])) {
            return Common::apiResponse(false, 'missing id_token', null, 422);
        }

        $appleKeys = Http::get('https://appleid.apple.com/auth/keys')->json();

        try {
            $decoded = JWT::decode(
                $data['id_token'],
                JWK::parseKeySet($appleKeys)
            );
        } catch (\Exception $e) {
            return Common::apiResponse(false, 'invalid apple token', null, 422);
        }

        $appleUserId = $decoded->sub;
        $email = $decoded->email ?? $data['email'] ?? null;
        $name  = $data['name'] ?? 'Apple User';

        try {
            [$user, $token] = $this->authService->loginWithApple(
                [
                    'email' => $email,
                    'name'  => $name,
                    'lat'   => $data['lat'] ?? null,
                    'long'  => $data['long'] ?? null,
                    'iso'   => $data['iso'] ?? null,
                    'uuid'  => $data['uuid'] ?? null,
                    'device_token' => $data['device_token'] ?? null,
                ],
                $appleUserId
            );
        } catch (\Exception $ex) {
            return Common::apiResponse(false, $ex->getMessage(), null, 422);
        }

        if (!$this->canLogin($user)) {
            return Common::apiResponse(false, 'you are blocked', [], 422);
        }

        $user->auth_token = $token;

        try {
            if ($user->device_token) {
                event(new DeviceTokenSent($user->id, $user->device_token));
            }
        } catch (\Throwable $e) {
            // Ignore event errors
        }

        try {
            AccountHelper::linkLoginAccountWithDevice($user->id, $user->device_token);
        } catch (\Throwable $e) {
            // Ignore device link errors
        }

        return Common::apiResponse(
            true,
            __('api_responses.logged'),
            [
                'id'            => $user->id,
                'is_first'      => (bool) ($user->is_points_first ?? false),
                'auth_token'    => $user->auth_token
            ]
        );
    }




    protected function loginWithHuawei($data)
    {
        try {
            [$user, $token] = $this->authService->loginWithHuawei($data);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 422);
        }
        $user->auth_token = $token;
        event(new DeviceTokenSent($user->id, $user->device_token));
        AccountHelper::linkLoginAccountWithDevice($user->id, $user->device_token);

        return Common::apiResponse(
            true,
            __('api_responses.logged'),
            [
                'id'            => $user->id,
                'is_first'      => @(bool)$user->is_points_first,
                'auth_token'    => $user->auth_token
            ]
        );
    }

    public function recallAccount(Request $request)
    {
        if (!$request['email'] && !$request['google_id']) return Common::apiResponse(false, 'messing parameter', 422);
        try {
            [$user, $token] = $this->authService->recallAccount($request);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 422);
        }
        $user->auth_token = $token;
        AccountHelper::linkLoginAccountWithDevice($user->id, $user->device_token);

        return Common::apiResponse(
            true,
            __('api_responses.logged'),
            [
                'id'            => $user->id,
                'is_first'      => @(bool)$user->is_points_first,
                'auth_token'    => $user->auth_token
            ]
        );
        return Common::apiResponse(true, 'logged in successfully', new MyDataResource($user), 200);
    }



    public function canLogin($user)
    {
        $status = $user instanceof User ? $user->status : ($user['status'] ?? null);

        return $status == 1;
    }
}
