<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\Ware;
use Illuminate\Http\Request;
use Modules\CP\Entities\CpLevelGift;

class LevelGiftController extends Controller
{
    public function index($cp_level_id){

        $search = request('search');
        $perPage = request('per_page') ?? 10;

        $result = CpLevelGift::where('vip_id', $cp_level_id)
        ->when($search,function($q)use($search){
            $q->where('id', $search);
        })
        ->paginate($perPage);

        return Common::apiResponse(true, 'Success', $result);
    }

    public function delete($cp_level_id, $id){
        CpLevelGift::where('vip_id', $cp_level_id)->findOrFail($id)->delete();

        return Common::apiResponse(true,'Success');
    }

    public function delete_all($cp_level_id,Request $request){
        $request->validate([
            'ids' => 'required'
        ]);

        $ids = explode(',', $request->ids);

        CpLevelGift::where('vip_id', $cp_level_id)->whereIn('id', $ids)->delete();

        return Common::apiResponse(true, 'Success');
    }

    public function store($cp_level_id, Request $request){

        $request->validate([
            'type' => 'required|in:ware,vip,coins,achievement',
            'item_id' => 'required',
            // 'coins' => 'nullable|integer|min:1',
            // 'achievement' => 'nullable|image',
            'expire' => 'nullable|integer|min:1',
            'gender' => 'required|in:all,male,female',
        ]);

        $data = [
            'vip_id' => $cp_level_id,
            'type' => $request->type,
            'expire' => $request->expire,
            'gender' => $request->gender,
        ];

        if ($request->type === 'ware') {
            $ware = Ware::find($request->item_id);
            if ($ware) {
                $data['item_id'] = $ware->id;
                $data['sub_type'] = match ($ware->type) {
                    4 => 'bubble',
                    5 => 'intro',
                    6 => 'frame',
                    default => null,
                };
            }
        } elseif ($request->type === 'vip') {
            $data['item_id'] = $request->item_id;
        } elseif ($request->type === 'coins') {
            $data['item_id'] = $request->coins;
        } elseif ($request->type === 'achievement' && $request->hasFile('achievement')) {
            $data['item_id'] = $request->file('achievement')->store('achievements', 'gcs');
        }

        $result = CpLevelGift::create($data);


        return Common::apiResponse(true, 'Success', $result);
    }

    public function update($cp_level_id, $id, Request $request){

        $request->validate([
            'type' => 'required|in:ware,vip,coins,achievement',
            'item_id' => 'nullable|integer',
            'coins' => 'nullable|integer|min:1',
            'achievement' => 'nullable|image',
            'expire' => 'nullable|integer|min:1',
            'gender' => 'required|in:all,male,female',
        ]);

        $data = [
            'vip_id' => $cp_level_id,
            'type' => $request->type,
            'expire' => $request->expire,
            'gender' => $request->gender,
        ];

        if ($request->type === 'ware') {
            $ware = Ware::find($request->item_id);
            if ($ware) {
                $data['item_id'] = $ware->id;
                $data['sub_type'] = match ($ware->type) {
                    4 => 'bubble',
                    5 => 'intro',
                    6 => 'frame',
                    default => null,
                };
            }
        } elseif ($request->type === 'vip') {
            $data['item_id'] = $request->item_id;
        } elseif ($request->type === 'coins') {
            $data['item_id'] = $request->coins;
        } elseif ($request->type === 'achievement' && $request->hasFile('achievement')) {
            $data['item_id'] = $request->file('achievement')->store('achievements', 'gcs');
        }

        $result = CpLevelGift::where('vip_id',$cp_level_id)->findOrFail($id);

        $result->update($data);

        return Common::apiResponse(true, 'Success');
    }
}
