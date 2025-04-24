<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Services\WhatsappOtp;
use App\Http\Services\WhatsappWebhook;
use App\Models\Code;
use App\Models\Country;
use App\Models\User;
use App\Models\WhatsappWebhookValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;
use Nette\Schema\ValidationException;
use App\Facades\UserHandling;
use App\Http\Resources\Api\V1\ServerResource;
use App\Models\Server;
use App\Models\ServerCountry;

class RegisterController extends Controller
{
    function countries()
    {
        $data = Country::select('id', 'name', 'e_name', 'flag')->get();
        return $data;
    }
    // function deletecountries()  {
    //     $users = User::all();
    //     foreach ($users as $user) {
    //         $user->country_id = null ;
    //         $user->save();
    //     }
    //     return 123;
    // }

    public function all_servers()
    {
        $results = Server::where('status',1)->orderBy('default','desc')->get();
        return Common::apiResponse(true,'',ServerResource::collection($results));
    }

    public function sendWhatsAapOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'                => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return Common::apiResponse(false, __('api_responses.invalid_data'), $validator->errors());
        }

        $validator = $validator->getData();
        $phone = $validator['phone'];
        //        if (User::query ()->where ('phone',$phone)->exists ()){
        //            return Common::apiResponse (0,'already exists',null,405);
        //        }
        try {
            (new WhatsappOtp())->sendOtpMessage($phone);
        } catch (ValidationException $e) {
            return Common::apiResponse(false, $e->getMessage());
        }

        return Common::apiResponse(true, __('messages.code_is_sent_to_your_phone'));
    }
    public function register(RegisterRequest $request)
    {
        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;

        error_log('Phone: ' . $phone);
        error_log('Code: ' . $request->code);

        if(!$phone || !$request->code)
        {
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $isValid  = $whatsappOtpService->isValidate($phone, $request->code);

        error_log('isValid: ' . ($isValid ? 'true' : 'false'));

        if (!$isValid) {
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $whatsappOtpService->resetCodes($phone);
        if (User::query()->where('phone', $request->phone)->exists()) {
            return Common::apiResponse(0, 'already exists', null, 405);
        }

        $user = User::query()->create(['phone' => $request->phone, 'password' => $request->password,]);
        $user = User::find($user->id);

        if (\request('tags') && is_array(\request('tags'))) {
            $user->tags()->attach(\request('tags'));
        }
        $user->save();
        if (!$request->country_id) {
            $country = Country::query()->where('phone_code', '101')->first();
            $user->country_id = @$country->id ?: 0;
            $user->save();
        }
        $token = $user->createToken('api_token')->plainTextToken;
        UserHandling::AddUserVip($user,'register');
        $user->auth_token = $token;
        $user->is_points_first = 1;


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

    /**
     * @param $credential
     * @return void
     */
    public function validateFirebaseOtp($credential): void
    {
        $credentialPath = public_path('firebase_credentials.json');

        $factory         = (new Factory())->withServiceAccount($credentialPath);
        $auth            = $factory->createAuth();
        $verifiedIdToken = $auth->verifyIdToken($credential);

        // Authentication token is valid
        $uid = $verifiedIdToken->claims()->get('sub');
    }


    public function whatsappWebhook(Request $request)
    {
        $data = $request->all();

        if ($request->status == 'validated'){
            $request['uuid'] = @$request->uuid ?? rand(1, 555555);
            $request['requested_at'] = Carbon::parse($request->requested_at)->toDateTimeString();
            $request['expires_at'] = Carbon::parse($request->expires_at)->toDateTimeString();
            WhatsappWebhookValidate::create($request->all());
            //create user
        }
        return response()->json();
    }

    public function registerWithWhatsapp(Request $request, WhatsappWebhook $whatsappWebhook)
    {
        $validator = Validator::make($request->all(), [
           'phone' => 'required',
           'password' => 'required',
        ]);

        if ($validator->fails()){
            return Common::apiResponse(false, implode(',',$validator->errors()->messages()));
        }

        $phone = $request->phone;

        if (User::query()->where('phone', $phone)->exists()) {
            return Common::apiResponse(0, __('api.exists'), null, 405);
        }

        $whatsappWebhookValidate = $whatsappWebhook->getLastValidatedPhone($phone);
        if (!$whatsappWebhookValidate){
            return Common::apiResponse(false, __('api.whatsappValidation'));
        }

        $user = User::query()->create([
            'password' => $request->password,
            'status' => 1,
            ...$whatsappWebhookValidate->getUserData()
                                      ]);

        $token = $user->createToken('api_token')->plainTextToken;
        try {
            UserHandling::AddUserVip($user, 'register_whats_app');
        } catch (\Exception $e) {
        }
        $user->auth_token = $token;
        $user->is_points_first = 1;

        return Common::apiResponse(
            true,
            __('api_responses.logged'),
            [
                'id'            => $user->id,
                'name' => $user->name ?? '',
                'is_first'      => @(bool)$user->is_points_first,
                'auth_token'    => $user->auth_token
            ]
        );
    }
}
