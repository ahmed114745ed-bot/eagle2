<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Public\Entities\RewardLevelInterval;

class RewardLevelIntervalController extends Controller
{
    public function index($reward_level_interval){

        $perPage = request('per_page') ?? 10;
        $rewards = RewardLevelInterval::where('level_interval_id', $reward_level_interval)->paginate($perPage);


        return Common::apiResponse(1, 'success', $rewards, 200);

    }

    public function show($reward_level_interval, $id){
        $reward = RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id);



        return Common::apiResponse(1, 'success', $reward, 200);

    }

    public function store($reward_level_interval, Request $request){


        $reward = RewardLevelInterval::create([
            'level_interval_id' => $reward_level_interval,
            'type' => $request->type,
            'target' => $request->target,
            'expire' => $request->expire,
        ]);


        return Common::apiResponse(1, 'success', $reward, 200);

    }

    public function update($reward_level_interval, Request $request, $id){


        RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id)->update([
            'type' => $request->type,
            'target' => $request->target,
            'expire' => $request->expire,
        ]);


        return Common::apiResponse(1, 'success', [], 200);

    }

    public function destroy($reward_level_interval, $id){
        RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id)->delete();


        return Common::apiResponse(1, 'success', [], 200);

    }
}
