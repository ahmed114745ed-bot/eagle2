<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Repositories\Room\RoomRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Log;

class AgoraController extends Controller
{

    public function __construct(public RoomRepository $roomRepository, public UserRepository $userRepository)
    {

    }
    public function RtcToken(Request $request){

        $request->validate([
            'channel' => 'required',
            'expir' => 'nullable'
        ]);
        $user =$request->user();

        if($request->has('expir')){

            $token = generateRtcToken($request->channel,$user->id, $request->expir);

            return Common::apiResponse(true,'Success',$token);
        }

        $token = generateRtcToken($request->channel,$user->id);

        return Common::apiResponse(true,'Success',$token);
    }

    public function webhook(Request $request){
        Log::info('agora webhook triggered', [
            $request->all()
        ]);
        return ;
        $agoraSignature = $request->header('Agora-Signature');
        Log::info("Agora-Signature: " . $agoraSignature);

        // التحقق من أن الطلب يحتوي على JSON صحيح
        $data = $request->json()->all();

        // التحقق من صحة البيانات المطلوبة
        if (!isset($data['eventType'], $data['payload'])) {
            return response()->json(['error' => 'Invalid JSON structure'], 400);
        }

        // استخراج البيانات
        $eventType = $data['eventType'];
        $uid = $data['payload']['uid'] ?? null;
        $channelName = $data['payload']['channelName'] ?? '';
        $clientSeq = $data['payload']['clientSeq'] ?? '';

        // تسجيل البيانات
        Log::info("Event code: $eventType, UID: $uid, Channel: $channelName, ClientSeq: $clientSeq");

    }
}
