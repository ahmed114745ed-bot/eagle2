<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Helpers\Common;
use App\Helpers\FirebaseValidate;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Services\WhatsappOtp;
use App\Http\Services\WhatsappWebhook;
use App\Models\Code;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        if (!$request->phone || !$request->password|| !$request->code) return Common::apiResponse (0,'missing params',null,422);
        $user = $request->user ();


        if ($user->phone != $request->phone) return Common::apiResponse (0,'phone number not register with your account',null,404);

        $rules = [
            'phone' => [
                'required',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Common::apiResponse(0, 'Validation failed', $validator->errors(), 422);
        }

        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;
        $isValid            = $whatsappOtpService->isValidate($phone, $request->code);
        if (!$isValid){
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $whatsappOtpService->resetCodes($phone);
        /*try {
            FirebaseValidate::validateIdToken($request['credential']);
        } catch (FailedToVerifyToken $e) {
            return Common::apiResponse(0, 'invalid credential', null, 422);
        } catch (\Exception $e) {
            // Error occurred while verifying the authentication token
            return Common::apiResponse(0, 'Un expected error', null, 422);
        }*/


        $user->password = $request->password;
        $user->save();
        return Common::apiResponse (1,'reset successful',new UserResource($user));
    }

    public function resetWhatsapp(Request $request, WhatsappWebhook $whatsappWebhook){
        $phone = $request->phone;
        if (!$phone || !$request->password) return Common::apiResponse (0, 'missing params', null, 422);
        $user = $request->user ();


        if ($user->phone != $phone) return Common::apiResponse (0, 'phone number not register with your account', null, 404);

        $rules = [
            'phone' => [
                'required',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Common::apiResponse(0, 'Validation failed', $validator->errors(), 422);
        }

        $whatsappWebhookValidate = $whatsappWebhook->getLastValidatedPhone($phone);
        if (!$whatsappWebhookValidate){
            return Common::apiResponse(false, __('current phone not verified'));
        }
        $user = User::query ()->where ('phone', $phone)->first ();

        $user->password = $request->password;
        $user->save();
        return Common::apiResponse (1,'reset successful',new UserResource($user));
    }
}
