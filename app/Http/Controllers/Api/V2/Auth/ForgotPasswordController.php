<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Helpers\Common;
use App\Helpers\FirebaseValidate;
use App\Http\Controllers\Controller;
use App\Http\Services\WhatsappOtp;
use App\Http\Services\WhatsappWebhook;
use App\Models\Code;
use App\Models\User;
use Illuminate\Http\Request;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class ForgotPasswordController extends Controller
{
    public function reset(Request $request){
        if (!$request->phone || !$request->password || !$request->code) return Common::apiResponse(0, 'missing params');
//        try {
//            FirebaseValidate::validateIdToken($request['credential']);
//        } catch (FailedToVerifyToken $e) {
//            return Common::apiResponse(0, 'invalid credential', null, 422);
//        } catch (\Exception $e) {
//            // Error occurred while verifying the authentication token
//            return Common::apiResponse(0, 'Un expected error', null, 422);
//        }
        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;
        $isValid            = $whatsappOtpService->isValidate($phone, $request->code);
        if (!$isValid){
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $whatsappOtpService->resetCodes($phone);
        $user = User::query ()->where ('phone',$request->phone)->first ();
        $user->password = $request->password;
        $user->save();
        return Common::apiResponse (1,'reset successful',null);
    }

    public function verifyCode(Request $request){
        if (!$request->phone ||  !$request->code) return Common::apiResponse(0, 'missing params');

        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;
        $isValid            = $whatsappOtpService->isValidate($phone, $request->code);
        if (!$isValid){
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }

        return Common::apiResponse (1,'valid code',null);
    }


    public function resetWhatsapp(Request $request, WhatsappWebhook $whatsappWebhook){
        if (!$request->phone || !$request->password) return Common::apiResponse(0, 'missing params');
        $phone = $request->phone;

        $whatsappWebhookValidate = $whatsappWebhook->getLastValidatedPhone($phone);
        if (!$whatsappWebhookValidate){
            return Common::apiResponse(false, __('current phone not verified'));
        }

        $user = User::query ()->where ('phone',$request->phone)->first ();
        $user->password = $request->password;
        $user->save();
        return Common::apiResponse (1,'reset successful',null);
    }
}
