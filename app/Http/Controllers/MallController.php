<?php

namespace App\Http\Controllers;

use App\Facades\CustomNotification;
use App\Helpers\Common;
use App\Models\OVip;
use App\Models\Pack;
use App\Models\User;
use App\Models\UserVip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MallController extends Controller
{
    public function buyVip(Request $request){
        if (!$request->vip_id ) return Common::apiResponse (0,__('api_responses.missing_params'),null,422);

        $vip = OVip::query ()->find ($request->vip_id);
        if (!$vip) return Common::apiResponse (0,__('api_responses.not_found'),null,404);
        $qty = $request->qty?:1;
        $total = $vip->price * $qty;
        $expire = $vip->expire;
        if ($expire == 0){
            $ex = 0;
        }else{
            $ex = now ()->addDays ($expire * $qty)->timestamp;
        }
        if ($request->type == 1){
            $type = 1;
            if (!$request->to_user) return Common::apiResponse (0,__('api_responses.missing_params'),null,422);
            $user_id = $request->to_user;
            $user = User::query ()->where ('uuid',$user_id)->first ();
            if (!$user) return Common::apiResponse (0,__('api_responses.not_found'),null,404);
            $user_id = $user->id;
            $sender= $request->user ();
            $sender_id = $sender->id;
            if ($sender->di < $total) return Common::apiResponse (0,__('api_responses.low_balance'),null,407);
            $from = $sender;
        }else{
            $type = 0;
            $user = $request->user ();
            $user_id = $user->id;
            $sender_id = 0;
            if ($user->di < $total) return Common::apiResponse (0,__('api_responses.low_balance'),null,407);
            $from = $user;
        }

        DB::beginTransaction ();
        try {
            $from->decrement ('di',$total);
            UserVip::query ()->where ('user_id',$user_id)->where ('level','<=',$vip->level)->delete ();
            Pack::query ()->where ('user_id',$user_id)->where ('expire','<=',time ())->delete ();
            UserVip::query ()->create (
                [
                    'type'=>$type,
                    'sender_id'=>$sender_id,
                    'user_id'=>$user_id,
                    'vip_id'=>$vip->id,
                    'level'=>$vip->level,
                    'expire'=>$ex,
                    'qty'=>$qty,
                    'price'=>$vip->price,
                    'total'=>$total
                ]
            );
            Common::handelVip ($vip,$user);
            DB::commit ();
            CustomNotification::vips($user, $ex, $vip->img);


            return Common::apiResponse (1,'done',null,201);
        }catch (\Exception $exception){
            DB::rollBack ();
            return Common::apiResponse (0,$exception->getMessage (),null,400);
        }


    }
}
