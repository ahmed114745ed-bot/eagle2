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
        
        return [
            'id' => $this->id,
            'diamond' => $this->diamonds,
            'level' => $this->level,
            'name' => $this->name,
            'img' => $this->img,
            'picked_level' => $user->hostLevelWinnerByLevelAndEvent($this->id) ? true : false,
            'remaining' => $remaining < 0 ? 0 : $remaining,
            'rewards' => WeeklyStarGift::collection($this->whenLoaded('rewards')),
        ];
    }

   
}
