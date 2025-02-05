<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgoraController extends Controller
{
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
}
