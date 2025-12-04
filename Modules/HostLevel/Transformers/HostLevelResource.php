<?php

namespace Modules\HostLevel\Transformers;


use App\Helpers\Common;
use App\Models\GiftLog;
use Modules\Events\Transformers\WeeklyStarGift;
use Illuminate\Http\Resources\Json\JsonResource;

class HostLevelResource extends JsonResource
{

    public function toArray($request)
    {
        $user = request()->user();
        $diamonds = $request->userDiamonds ?? 0;
        $remaining = $this->diamonds - $diamonds;
        $eventType = Common::getSettingValue('host_level_type') ?? 'daily';
        $lastPick = $user->lastHostLevelWinnerByEvent($eventType)->first();
        return [
            'id' => $this->id,
            'diamond' => $this->diamonds,
            'level' => $this->level,
            'name' => $this->name,
            'img' => $this->img,
            'picked_level' => $user->hostLevelWinnerByLevelAndEvent($this->id) ? true : false,
            'remaining' => $remaining < 0 ? 0 : $remaining,
            'progress' => $lastPick
                ? ($this->id == $lastPick->hostLevel->id
                    ? 1
                    : $this->progress($this->diamonds, $diamonds, $lastPick->hostLevel->diamonds)
                )
                : 1,
            'rewards' => WeeklyStarGift::collection($this->whenLoaded('rewards')),
        ];
    }

    public function progress($nextDiamonds, $diamonds, $lastPickDiamonds)
    {
        $exactlyValue    = $nextDiamonds;
        $progressNext    = $nextDiamonds - $lastPickDiamonds;
        $progressCurrent = $diamonds - $lastPickDiamonds;
        $prog = $progressNext != 0 ? ($progressCurrent / $progressNext) : 0;

        if ($prog >= 1) {
            $bar = 1;
        } else {
            $bar = round($prog, 1);
        }
        return  $exactlyValue == 0 ? 1 : ($bar < 0 ? 1 : $bar);
    }
}
