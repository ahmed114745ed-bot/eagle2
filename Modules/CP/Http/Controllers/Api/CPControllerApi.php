<?php

namespace Modules\CP\Http\Controllers\Api;

use App\Helpers\Common;
use App\Models\Vip;
use Auth;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CP\Entities\CpInfo;
use Modules\CP\Http\Services\CpServices;
use Modules\CP\Transformers\CpRankingResource;

class CPControllerApi extends Controller
{

    public function cancel_cp(Request $request)
    {
        $user = $request->user();
        (new CpServices ())->cancelation($user);
        return Common::apiResponse(true, __("cp::api.cancel_cp_done"),[]);
    }

    public function cp_ranking(Request $request)
    {
        $user = $request->user();
        $type = $request->type ?? "today";
        $data = (new CpServices)->ranking($type);
        $result = CpRankingResource::collection($data);
        $resourceArray = $result->resolve();

        $top = array_slice($resourceArray, 0, 3);
        $other = array_slice($resourceArray, 3);

        $dividedData = [
            'top' => $top,
            'other' => $other
        ];
        return Common::apiResponse(true, 'success',$dividedData);
    }

    public function cp_level()
    {
        $user = Auth::user();
        $cp = CpInfo::where(fn($q)=>$q->where("user_one_id",$user->id)->orWhere("user_two_id",$user->id))->where("status",1)->first();
        if (!$cp) {
            return Common::apiResponse(false, __("api.dont_have_cp"),[]);
        }
        $currentLevel = Vip::where("type",3)->where("level",$cp->level)->first();
        $secondLevel = Vip::where("type",3)->where("level",">",$cp->level)->first();
        $remaining = 0;
        $progress = 0;
        if($secondLevel != null){
            $remaining = $secondLevel->exp - $cp->exp;
            $exatlyValue = $secondLevel->exp - $currentLevel->exp ;
            $progress = $remaining / $exatlyValue ;
        }
        $data = [
            'current_level'  => $cp->level,
            'current_exp'    => $currentLevel->exp,
            'current_img'    => $currentLevel->img,
            'next_level'     => @$secondLevel->level,
            'next_exp'       => @$secondLevel->exp,
            'next_img'       => @$secondLevel->img,
            'remaining'         => @$remaining ,
            'progress'          => @$progress,
            'userOne'           =>[
                "id" => $cp->userOne?->id,
                "uuid" => $cp->userOne?->uuid,
                "name" => $cp->userOne?->name,
                "image" => $cp->userOne?->profile?->avatar,
            ],
            'userTwo'           =>[
                "id" => $cp->userTwo?->id,
                "uuid" => $cp->userTwo?->uuid,
                "name" => $cp->userTwo?->name,
                "image" => $cp->userOne?->profile?->avatar,
            ],
        ];
        return Common::apiResponse(true, 'success',$data);
    }
}
