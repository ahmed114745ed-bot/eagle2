<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use App\Repositories\Room\RoomRepository;
use App\Repositories\User\UserRepository;
use App\Tik\Services\EnteranceRoomServices;
use Illuminate\Http\Request;
use Log;

class AgoraController extends Controller
{
    protected $enteranceRoomService;

    public function __construct(public RoomRepository $roomRepository, public UserRepository $userRepository, EnteranceRoomServices $enteranceRoomService)
    {
        $this->enteranceRoomService = $enteranceRoomService;
    }
    public function RtcToken(Request $request)
    {

        $request->validate([
            'channel' => 'required',
            'expir' => 'nullable'
        ]);
        $user = $request->user();
        $userId = 'user-' .  $user->id;

        if ($request->has('expir')) {

            $token = generateRtcToken($request->channel, $user->id, $request->expir);

            return Common::apiResponse(true, 'Success', $token);
        }

        $token = generateRtcToken($request->channel, $user->id);
        $rtmToken = generateAgoraRtmToken( $request->channe ,$user->id);


        return Common::apiResponse(true, 'Success', [
            'rtc_token' => $token,
            'rtm_token' => $rtmToken,
            'appId' => config('services.agora.app_id'),
            'appCertificate' =>  config('services.agora.app_certificate'),

        ]);

    }

    public function webhook(Request $request)
    {
        $library = Common::getConfig('library');
        if ($library == 2) return  Common::apiResponse(false, 'you used pusher');

        return $this->enteranceRoomService->updateRoomCountFromAgora($request);
        // $agoraSignature = $request->header('Agora-Signature');

        // // التحقق من أن الطلب يحتوي على JSON صحيح
        // $data = $request->json()->all();

        // // التحقق من صحة البيانات المطلوبة
        // if (!isset($data['eventType'], $data['payload'])) {
        //     return response()->json(['error' => 'Invalid JSON structure'], 400);
        // }

        // // استخراج البيانات
        // $eventType = $data['eventType'];
        // $uid = $data['payload']['uid'] ?? null;
        // $channelName = $data['payload']['channelName'] ?? '';
        // $clientSeq = $data['payload']['clientSeq'] ?? '';

        // // تسجيل البيانات
        //     agora webhook triggered
        //     [{
        //        "noticeId":"1414157015:2753369:102",
        //        "notifyMs":1741600266185,
        //        "eventType":102,
        //        "sid":"FA7703A9A07A4DC79B0AF37AB066EB30",
        //        "payload":{
        //            "lastUid":621,
        //            "channelName":"922",
        //            "ts":1741600265},
        //        "productId":1
        //    }]
      
    }



}
