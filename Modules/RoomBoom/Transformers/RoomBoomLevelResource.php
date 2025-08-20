<?php

namespace Modules\RoomBoom\Transformers;

use App\Http\Resources\Api\V1\TopUsersRankResource;
use App\Models\GiftLog;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomBoomLevelResource extends JsonResource
{
    public function toArray($request)
    {
        $data =  [
            'id' => $this->id,
            'level' => $this->level,
            'min_target' => $this->whenHas('min_target'),
            'target' => $this->whenHas('target'),
            'video' => $this->video,
            'room_booms' => RoomBoomResource::collection($this->whenLoaded('roomBooms')),
            'rewards' => RoomBoomRewardResource::collection($this->whenLoaded('roomBoomRewards')),
        ];

        $this->appendTopContributors($data);

        return $data;
    }

    protected function appendTopContributors(array &$data): void
    {
        if (! $this->relationLoaded('roomBooms')) {
            return;
        }

        $roomBoom = $this->roomBooms->first();

        if (! $roomBoom || ! $roomBoom->ended_at) {
            $data['top_contributors'] = [];
            return;
        }

        $topContributors = GiftLog::select('sender_id',
            DB::raw('SUM(giftPrice) as total_gift'),
            DB::raw('MIN(created_at) as first_contribution')
        )
            ->where('room_id', $roomBoom->totalRoomGift->room_id)
            ->where('room_boom_level', $this->level)
            ->where('start_boom_ranking', 1)
            ->where('created_at', '>=', Carbon::today())
            ->groupBy('sender_id')
            ->orderByDesc('total_gift')
            ->orderBy('first_contribution', 'asc')
            ->limit(3)
            ->get();

        $users = $topContributors->map(function($contributor) {
            $user = User::with('profile')->find($contributor->sender_id);
            if ($user) {
                $user->total_gift = $contributor->total_gift;
                return $user;
            }
            return null;
        })->filter();

        $data['top_contributors'] = TopUsersRankResource::collection($users);
    }
}
