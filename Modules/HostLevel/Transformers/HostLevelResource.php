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
        $diamonds = $this->computeDiamonds($user->id);
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

    private function computeDiamonds($userId)
    {
        $eventType = $this->getEventType();

        return  GiftLog::where('receiver_id', $userId)
            ->filterByEventType($eventType)
            ->selectRaw('receiver_id, SUM(giftNum * giftPrice) AS total_diamond')->groupBy("receiver_id")
            ->value('total_diamond');
    }

    private function getEventType(): string
    {
        return Common::getSettingValue('host_level_type') ?? 'daily';
    }
}
