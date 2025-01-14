<?php

namespace App\Http\Controllers\utd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Public\Entities\RewardLevelInterval;

class RewardLevelIntervalController extends Controller
{
    public function index($reward_level_interval){

        $perPage = request('per_page') ?? 10;
        $rewards = RewardLevelInterval::where('level_interval_id', $reward_level_interval)->paginate($perPage);

        return response()->json([
            'message' => 'Rewards returned successfully',
            'status' => 'success',
            'data' => $rewards
        ]);
    }

    public function show($reward_level_interval, $id){
        $reward = RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id);

        return response()->json([
            'message' => 'Reward returned successfully',
            'status' => 'success',
            'data' => $reward
        ]);
    }

    public function store($reward_level_interval, Request $request){


        $reward = RewardLevelInterval::create([
            'level_interval_id' => $reward_level_interval,
            'type' => $request->type,
            'target' => $request->target,
            'expire' => $request->expire,
        ]);

        return response()->json([
            'message' => 'Reward created successfully',
            'status' => 'success',
            'data' => $reward
        ]);
    }

    public function update($reward_level_interval, Request $request, $id){


        RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id)->update([
            'type' => $request->type,
            'target' => $request->target,
            'expire' => $request->expire,
        ]);

        return response()->json([
            'message' => 'Reward updated successfully',
            'status' => 'success',
        ]);
    }

    public function destroy($reward_level_interval, $id){
        RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id)->delete();

        return response()->json([
            'message' => 'Reward deleted successfully',
            'status' => 'success',
        ]);
    }
}
