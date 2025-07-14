<?php

namespace Modules\Events\Http\Controllers;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\GiftLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Events\Entities\PkEvent;
use Modules\Events\Transformers\PkEventResource;
use Modules\Events\Transformers\PkEventTopResource;
use Modules\Events\Transformers\PkGiftResource;
use Modules\Events\Transformers\UserWeeklyStar;

class PkEventController extends Controller
{
    public function topUsersPKEvent(Request $request)
    {
        $pkEvent = PkEvent::currentEvent()->first();

        if (!$pkEvent) {
            return Common::apiResponse(0, __('there is no event now'), null, 422);
        }

        $user = $request->user();
        $userId = $user->id;
        $type = (int) $request->input('type', 1);

        // Determine column and relation based on type
        $typeMap = [
            1 => ['column' => 'sender_id', 'relation' => 'sender'],
            2 => ['column' => 'roomowner_id', 'relation' => 'roomOwner.ownerRoom'],
            3 => ['column' => 'receiver_id', 'relation' => 'receiver'],
        ];

        $columnInfo = $typeMap[$type] ?? $typeMap[1];
        $groupColumn = $columnInfo['column'];
        $relation = $columnInfo['relation'];

        // Base query
        $query = GiftLog::query()
            ->selectRaw("SUM(giftPrice) as totalGiftNum, {$groupColumn}")
            ->where('pk', 1)
            ->whereBetween('created_at', [$pkEvent->start_date, $pkEvent->end_date])
            ->groupBy($groupColumn)
            ->orderByDesc('totalGiftNum');

        // Eager load relation based on type
        if ($type === 2) {
            $query->with(['roomOwner.ownerRoom:id,uid,room_name,room_cover']);
        } else {
            $query->with([$relation => function ($q) {
                $q->select('id', 'name', 'avatar');
            }]);
        }

        $topEntries = $query->get();

        // First 20 top entries
        $top20 = $topEntries->take(20);

        // Check if user exists in top list
        $userExists = $top20->pluck($groupColumn)->contains($userId);

        // Get user's data only if not already in top
        $userData = null;
        if (!$userExists) {
            $userData = $topEntries->firstWhere($groupColumn, $userId);
        }

        // Standardize relation to "user" for resource collection
        $top20->each(function ($item) use ($relation) {
            $item->setRelation('user', data_get($item, $relation));
            foreach (explode('.', $relation) as $rel) {
                $item->unsetRelation($rel);
            }
        });

        return Common::apiResponse(1, '', [
            'top' => PkEventTopResource::collection($top20),
            'user' => $userExists ? null : new UserWeeklyStar($user, $userData),
        ]);
    }


    public function topDetails()
    {
        $nowDate = Carbon::now();
        $pkEvent = PkEvent::currentEvent()->with(['rewards']) ->first();
        if (!$pkEvent) {
            return Common::apiResponse(0, __('there is no event now'), null, 422);
        }
        // dd($pkEvent->rewards);
        $rewards = collect($pkEvent->rewards);

        $data    = [
            'pk_star' => new PkGiftResource($rewards->where("pk_type", 'pk-star')),
            'pk_king' => new PkGiftResource($rewards->where("pk_type", 'pk-king')),
            'pk_room' => new PkGiftResource($rewards->where("pk_type", 'pk-room')),
        ];
        return Common::apiResponse(1, '', $data);
    }


    public function pkEvent()
    {
        $yesterdayStart = Carbon::yesterday()->startOfDay()->timezone(config('app.owner_timezone'))->copy()->toDateTimeString();
        $yesterdayEnd = Carbon::yesterday()->copy()->toDateTimeString();
        $pkEvent   = PkEvent::currentEvent()->first();

        if (!$pkEvent) return Common::apiResponse(0, __('there is no event now'), null, 422);

        $dataSender = GiftLog::with('sender')->select(DB::raw('sum(giftPrice) as totalGiftNum'), 'sender_id')
            ->groupBy('sender_id')->where('pk', 1)->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->orderByDesc('totalGiftNum')->first();
        $dataRoomOwner =
            GiftLog::where('roomowner_id', '!=', 0)->with(['roomOwner.ownerRoom:id,uid,room_name,room_cover'])->select(DB::raw('sum(giftPrice) as totalGiftNum'), 'roomowner_id',)
            ->groupBy('roomowner_id')->where('pk', 1)->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->orderByDesc('totalGiftNum')->first();
        $data          = [
            'winner_previous_event' => [
                "PK_king" => $dataSender->sender->profile->avatar ?? '',
                "Room_pk" => $dataRoomOwner->roomOwner->ownerRoom->room_cover ?? ''
            ],
            'pk_event'              => new PkEventResource($pkEvent),
        ];
        return Common::apiResponse(1, '', $data);
    }
}
