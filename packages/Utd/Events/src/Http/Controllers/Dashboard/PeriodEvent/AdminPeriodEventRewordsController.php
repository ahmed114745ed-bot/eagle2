<?php

namespace Utd\Events\Http\Controllers\Dashboard\PeriodEvent;

use App\Http\Controllers\Controller;
use App\Traits\Dashboard\DashBoardTrait;
use Illuminate\Http\Request;
use Utd\Events\Entities\Reward;
use Utd\Events\Transformers\Dashboard\AdminWeeklyStarEventsResource;
use Utd\Pk\Http\Resources\AdminPKEventRewardsResource;

class AdminPeriodEventRewordsController extends Controller
{
    use DashBoardTrait;

    public function index(Request $request)
    {
        $id = $request->input('id');
        $level1 = Reward::where('weekly_star_id', $id)->where('level', 1)->get();
        $level2 = Reward::where('weekly_star_id', $id)->where('level', 2)->get();
        $level3 = Reward::where('weekly_star_id', $id)->where('level', 3)->get();

        return [
            'level1' => AdminPKEventRewardsResource::collection($level1),
            'level2' => AdminPKEventRewardsResource::collection($level2),
            'level3' => AdminPKEventRewardsResource::collection($level3),
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:weekly_stars,id',
            'expire' => 'required|numeric',
            'level' => 'required|numeric',
            'type' => 'required',
            'img' => 'nullable',
        ]);
        $img = null;
        if ($request->type !== 'achievement') {
            $request->validate([
                'target' => 'required|numeric',
            ]);
        } else {
            $img = $request->hasFile('img') ? $this->store_img($request->file('img'), 'events') : null;
        }
        Reward::insert([
            'weekly_star_id' => $request->event_id,
            'expire' => $request->expire,
            'level' => $request->level,
            'type' => $request->type,
            'target' => $request->type !== 'achievement' ? $request->target : $img ?? 'sasa',
        ]);

        return $img;
    }

    public function show(string $id)
    {
        $data = Reward::find($id);

        return new AdminWeeklyStarEventsResource($data);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'event_id' => 'required|exists:weekly_stars,id',
            'expire' => 'required|numeric',
            'level' => 'required|numeric',
            'type' => 'required',
            'img' => 'nullable',
        ]);

        $pkReward = Reward::findOrFail($id);

        if ($request->type !== 'achievement') {
            $request->validate([
                'target' => 'required|numeric',
            ]);
        } else {
            if ($request->hasFile('img')) {
                $img = $request->hasFile('img') ? $this->store_img($request->file('img'), 'events') : null;
            } else {
                $img = $pkReward->target;
            }
        }

        $pkReward->update([
            'weekly_star_id' => $request->event_id,
            'expire' => $request->expire,
            'level' => $request->level,
            'type' => $request->type,
            'target' => $request->type !== 'achievement' ? $request->target : $img ?? $pkReward->target,
        ]);

        return $img;
    }

    public function destroy(string $id)
    {
        $PkReward = Reward::find($id);
        if ($PkReward->type === 'achievement' && $PkReward->target) {
            $this->delete_img($PkReward->target);
        }
        $PkReward->delete();

        return 200;
    }
}
