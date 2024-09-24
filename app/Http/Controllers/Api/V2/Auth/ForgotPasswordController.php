<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Models\Code;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Helpers\FirebaseValidate;
use App\Http\Services\WhatsappOtp;
use App\Http\Controllers\Controller;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class ForgotPasswordController extends Controller
{
    public function reset(Request $request){
        if (!$request->phone || !$request->password || !$request->code) return Common::apiResponse(0, 'missing params');

        $whatsappOtpService = new WhatsappOtp();
        $phone              = $request->phone;
        $isValid            = $whatsappOtpService->isValidate($phone, $request->code);
        if (!$isValid){
            return Common::apiResponse(false, __('api_responses.invalid_code'));
        }
        $whatsappOtpService->resetCodes($phone);
        $user = User::query ()->where ('phone',$request->phone)->first ();
        if (!$user)  return Common::apiResponse(0, 'validate your phone', null, 422);
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
}
