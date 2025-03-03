<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\User;
use Firebase\JWT\JWT;
use App\Helpers\Common;
use App\Tik\Services\AuthService;
use App\Http\Services\WhatsappOtp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use Google_Client;
use Modules\SwitchAccount\Http\Services\SwitchAccountServices;
use Google\Client as GoogleClient;


class AuthController extends Controller
{

    public function __construct(private AuthService $authService)
    {
    }

    public function register(RegisterRequest $request)
    {
        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;


        // \Log::info('Phone:', ['phone' => $phone]);
        // \Log::info('Code:', ['code' => $request->code]);

        // error_log('Phone: ' . $phone);
        // error_log('Code: ' . $request->code);


        if (!$phone || !$request->code) {
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $isValid  = $whatsappOtpService->isValidate($phone, $request->code);

        // \Log::info('isValid:', ['isValid' => $isValid]);
        // error_log('isValid: ' . ($isValid ? 'true' : 'false'));

        if (!$isValid) {
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $whatsappOtpService->resetCodes($phone);
        try {
            [$user, $token] = $this->authService->registration($request);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
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
            'is_multi' => @$request->is_multi ?? false,
            'notification_id' => @$request->notification_id,

        ];
        switch ($request['type']) {
            case 'phone_pass':
                $fields = ['phone' => $request['phone'], 'password' => $request['password'], 'device_token' => $request['device_token']];
                $fields = array_merge($globalKeys, $fields);
                return $this->loginWithPhonePassword($fields);
            case 'google':
                $fields = ['name' => $request->name, 'email' => $request->email, 'google_id' => $request['google_id'], 'device_token' => $request['device_token'],'id_token' => $request['id_token']];
                $fields = array_merge($globalKeys, $fields);
                return $this->loginWithGoogle($fields);
            case 'apple':
                $fields = ['name' => $request->name, 'apple_id' => $request->apple_id, 'device_token' => @$request['device_token'], 'email' => @$request->email, 'user_id', @$request['user_id']];
                $fields = array_merge($globalKeys, $fields);
                return $this->loginWithApple($fields);
            case 'huawei':
                $fields = ['name' => $request->name, 'email' => $request->email, 'huawei_id' => $request->huawei_id, 'id_token' => $request->id_token];
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

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        if (!$this->canLogin($user)) {
            return Common::apiResponse(false, 'you are blocked', [], 408);
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
        return Common::apiResponse(true, 'logged in successfully', new MyDataResource($user), 200);
    }

    protected function loginWithGoogle($data)
    {

        try {
            [$user, $token, $resource] = $this->authService->loginWithGoogle($data);
            if ($resource!= null) {
                Common::apiResponse(false, 'email already taken', $resource, 405);
            }
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        // \Log::info('This is login from google :  ' . gettype($user). ' '. json_encode($user));

        if (!$this->canLogin($user)) {
            return Common::apiResponse(false, 'you are blocked', [], 408);
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
        return Common::apiResponse(true, 'logged in successfully', new MyDataResource($user), 200);
    }





    protected function loginWithApple($data)
    {
        $fields = $data;
        $unique_id = $data['apple_id'];
        $teamId = config('apple.apple_team_id'); // Use the correct environment variable name
        $keyId = "PAN9HH2A6X"/*config('apple.apple_key_id')*/; // Use the correct environment variable name
        $clientId = 'com.tikkchat.app'; // Use the correct environment variable name
        $redirectUri = config('apple.apple_redirect_uri'); // Use the correct environment variable name
        $iat = strtotime('now');
        $exp = strtotime('+60days');

        $keyContent = file_get_contents(config('apple.service_file'));

        $token = JWT::encode([
            'iss' => $teamId,
            'iat' => $iat,
            'exp' => $exp,
            'aud' => 'https://appleid.apple.com',
            'sub' => $clientId,
        ], $keyContent, 'ES256', $keyId);

        try {
            $res = Http::asForm()->post('https://appleid.apple.com/auth/token', [
                'grant_type' => 'authorization_code',
                'code' => $unique_id,
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $token,
            ]);

            $claims = explode('.', $res['id_token'])[1];
            $data = json_decode(base64_decode($claims), true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'wrong credential.', 'message' => $e->getMessage()], 403);
        }

        try {
            [$user, $token] = $this->authService->loginWithApple($data, $unique_id);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        if (!$this->canLogin($user)) {
            return Common::apiResponse(false, 'you are blocked', [], 408);
        }

        $user->auth_token = $token;

        return Common::apiResponse(true, '', new MyDataResource($user), 200);
    }


    protected function loginWithHuawei($data)
    {

        try {
            [$user, $token] = $this->authService->loginWithHuawei($data);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
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

    public function recallAccount(Request $request)
    {
        if (!$request['email'] && !$request['google_id']) return Common::apiResponse(false, 'messing parameter', 400);
        try {
            [$user, $token] = $this->authService->recallAccount($request);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
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
        return Common::apiResponse(true, 'logged in successfully', new MyDataResource($user), 200);
    }



    public function canLogin($user)
    {
        $status = $user instanceof User ? $user->status : ($user['status'] ?? null);

        return $status == 1;
    }
}
