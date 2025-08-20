<?php

namespace Modules\RoomBoom\Transformers;

use App\Http\Resources\Api\V1\TopUsersRankResource;
use App\Models\Gift;
use App\Models\GiftLog;
use App\Models\User;
use App\Models\Ware;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomBoomResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'total_gifts_value' => $this->total_gifts_value,
            'level' => $this->roomBoomLevel ? $this->roomBoomLevel->level : null,
            'top_contributors' => TopUsersRankResource::collection($this->getTopContributors()),
        ];
    }

    protected function getTopContributors()
    {
        $topContributors = GiftLog::select('sender_id',
            DB::raw('SUM(giftPrice) as total_gift'),
            DB::raw('MIN(created_at) as first_contribution')
        )
            ->where('room_id', $this->totalRoomGift->room_id)
            ->where('room_boom_level', $this->roomBoomLevel->level)
            ->where('start_boom_ranking', 1)
            ->where('created_at', '>=', Carbon::today())
            ->groupBy('sender_id')
            ->orderByDesc('total_gift')
            ->orderBy('first_contribution', 'asc')
            ->limit(3)
            ->get();

        return $topContributors->map(function($contributor) {
            $user = User::with('profile')->find($contributor->sender_id);
            if ($user) {
                $user->total_gift = $contributor->total_gift;
                return $user;
            }
            return null;
        })->filter();
    }
}
