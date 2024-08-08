<?php

namespace Modules\Whatsapp\Http\Controllers;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Services\WhatsappOtp;
use Illuminate\Http\Request;
use Modules\Whatsapp\Services\WhatsappOTPService;
use Nette\Schema\ValidationException;

class OtpController extends Controller
{
    public function __construct(private WhatsappOTPService $whatsappOTPService)
    {
    }

    public function sendMessageClient(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'code' => 'required|string|max:10',
            'phone' => 'required|string|max:30',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors()->all(),402);
        }
        $user    = \Auth::user();
        $phoneId         = @$user->phone_id;
        $phone         = $request->phone;
        $code = $request->code;

        $result = $this->whatsappOTPService->sendMessage($phone, $code, $phoneId);

        return response()->json(['success' => $result], $result? 200:402);
    }


    public function sendWhatsAapOtp(Request $request)
    {
        $validator = \Validator::make($request->all(), [
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
}
