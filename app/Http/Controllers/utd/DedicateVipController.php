<?php

namespace App\Http\Controllers\utd;

use App\Facades\CustomNotification;
use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\OVip;
use App\Models\User;
use App\Models\UserVip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DedicateVipController extends Controller
{
    public function index(){
        $search = request('search');
        $results = OVip::when($search, function($q)use($search){
            $q->where('id', $search);
        })
        ->paginate(10);

        return Common::apiResponse(true, 'Success', $results);
    }


    public function delete_all(Request $request){

        $request->validate([
            'ids' => 'required'
        ]);

        $ids = explode(',', $request->ids);

        OVip::whereIn('id', $ids)->delete();

        return Common::apiResponse(true,'Success');
    }


    public function dedicate($id, Request $request){

        $user = User::query()->searchByUuid($request->user_uuid)->first();
            // admin only put to user vip greater than 30 days
            /* if (!Admin::user()->can('*') && $request->days > 30){
                return $this->response()->error(__('dashboard.addAchivement'))->refresh();
            } */
           $vip = OVip::findOrFail($id);
            DB::beginTransaction();


            $enableVipAuto = Common::getConf('enable_vip_auto') ?? "false";
            $is_used = $enableVipAuto === "true" ? 1 : 0;

            try {
                $uniqueAttributes = [
                    'sender_id' => 0,
                    'user_id'   => $user->id,
                    'vip_id'    => $vip->id,
                    'level'     => $vip->level,
                ];
                $userVip = UserVip::query()->where($uniqueAttributes)->first();
                if (!$userVip) {
                    UserVip::query()->create(
                        [
                            ...$uniqueAttributes,
                            'type'   => 1,
                            'expire' => Carbon::now()->addDays($request->days ?: 1)->timestamp,
                            'qty'    => 1,
                            'price'  => 0,
                            'total'  => 0,
                            'is_used'  => $is_used,
                            'dash_user_id'  => $request->dash_user_id,
                        ]
                    );
                } else {
                    $userVip->qty++;
                    if($userVip->expire > now()->timestamp){
                        $userVip->expire += ($request->days * 86400);
                        $userVip->is_used += $is_used;
                    }else{
                        $userVip->expire = now()->timestamp + ($request->days * 86400);
                        $userVip->is_used += $is_used;

                    }
                    $userVip->save();
                }
                Common::handelVip($vip, $user, expire: $request->days ?? 1);

                DB::commit();
                CustomNotification::vips($user, $request->days, $vip->img);
                return Common::apiResponse(true,__('dashboard.successful'));
            } catch (\Exception $exception) {
                echo($exception->getMessage());
                DB::rollBack();
                return Common::apiResponse(false,'خطا.');
            }

    }
}
