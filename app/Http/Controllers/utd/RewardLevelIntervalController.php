<?php

namespace App\Http\Controllers\utd;

use App\Models\OVip;
use App\Models\Ware;
use App\Helpers\Common;
use App\Enums\IntervalLevel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Public\Entities\RewardLevelInterval;

class RewardLevelIntervalController extends Controller
{



    public function index($reward_level_interval){

        $id = request('id');
        $perPage = request('per_page') ?? 10;
        $rewards = RewardLevelInterval::when($id, function ($query, $id) {
            return $query->where('id', $id);
        })->where('level_interval_id', $reward_level_interval)->paginate($perPage);

        return Common::apiResponse(1, 'success', $rewards, 200);
    }

    public function show($reward_level_interval, $id)
    {
        $reward = RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id);



        return Common::apiResponse(1, 'success', $reward, 200);
    }

    public function store($reward_level_interval, Request $request)
    {


        $reward = RewardLevelInterval::create([
            'level_interval_id' => $reward_level_interval,
            'type' => $request->type,
            'target' => $request->target,
            'expire' => $request->expire,
        ]);


        return Common::apiResponse(1, 'success', $reward, 200);
    }

    public function update($reward_level_interval, Request $request, $id)
    {


        RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id)->update([
            'type' => $request->type,
            'target' => $request->target,
            'expire' => $request->expire,
        ]);


        return Common::apiResponse(1, 'success', [], 200);
    }

    public function destroy($reward_level_interval, $id)
    {
        RewardLevelInterval::where('level_interval_id', $reward_level_interval)->findOrFail($id)->delete();


        return Common::apiResponse(1, 'success', [], 200);
    }

    public function allType()
    {

        $data = IntervalLevel::getTranslatedOptions();
        return Common::apiResponse(1, 'success', $data, 200);
    }

    public function wareInterval()
    {
        $ops = [];
        $wares = Ware::query()->select(['id', 'name', 'type'])->whereIn('type', [4, 5, 6])->get();
        foreach ($wares as  $ware) {
            $ops[$ware->id] = $ware->name . '_' . $ware->id;

            if ($ware->type == 4) {
                $ops[$ware->id] .= '_' . 'frame';
            } elseif ($ware->type == 5) {
                $ops[$ware->id] .= '_' . 'bubble';
            } elseif ($ware->type == 6) {
                $ops[$ware->id] .= '_' . 'intro';
            }
        }

        $data = $ops;
        return Common::apiResponse(1, 'success', $data, 200);
    }

    public function vipInterval()
    {
        $vips = OVip::query()->select('id', 'name')->get();
        foreach ($vips as  $vip) {
            $ops[$vip->id] = $vip->name;
        }
        
        $data = $ops;
        return Common::apiResponse(1, 'success', $data, 200);
    }
}
