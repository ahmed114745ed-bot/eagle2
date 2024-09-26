<?php

namespace App\Http\Controllers\Api\V2\Auth;

use Exception;
use App\Models\User;
use App\Helpers\Common;
use App\Models\Country;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Http\Services\WhatsappOtp;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Http\Requests\Api\V2\Auth\RegisterRequest;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class RegisterController extends Controller
{
    function countries()
    {
        $data = Country::select('id', 'name', 'e_name', 'flag')->get();
        return $data;
    }
    public function register(RegisterRequest $request)
    {
        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;
        $isValid            = $whatsappOtpService->isValidate($phone, $request->code);

        if (!$isValid) {
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $whatsappOtpService->resetCodes($phone);
        if (User::query()->where('phone', $phone)->exists()) {
            return Common::apiResponse(0, 'already exists', null, 405);
        }
        $user = User::query()->create(
            ['phone' => $phone, 'password' => $request->password]

        );
        $user = User::find($user->id);

        if (\request('tags') && is_array(\request('tags'))) {
            $user->tags()->attach(\request('tags'));
        }
        $user->is_points_first = 1;
        $user->is_logout = 0;
        $user->save();
        if (!$request->country_id) {
            $country = Country::query()->where('phone_code', '101')->first();
            $user->country_id = @$country->id ?: 0;
            $user->save();
        }
        $token = $user->createToken('api_token')->plainTextToken;
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
    public function sendWhatsAapOtp(Request $request)
    {
        $token = $request->header('Authorization');
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
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return Common::apiResponse(true, __('messages.code_is_sent_to_your_phone'));
    }
}
