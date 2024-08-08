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
        $nowDate = Carbon::now();
        $pkEvent = PkEvent::currentEvent()->first();
        if (!$pkEvent) return Common::apiResponse(0, __('there is not event now'), null, 422);

        $columnRelations = [
            1 => 'sender',
            3 => 'receiver',
            2 => 'roomOwner',
        ];

        $relation = $columnRelations[$request['type']] ?? 'sender';
        $startDay = $pkEvent->start_date;
        $endDay   = $pkEvent->end_date;
        if ($request->type == 1) {

            $data = GiftLog::with('sender')->select([DB::raw('sum(giftPrice) as totalGiftNum'), 'sender_id'])
                ->groupBy('sender_id')->where('pk', 1)->whereBetween('created_at', [$startDay, $endDay])
                ->orderByDesc('totalGiftNum')->get();
        } elseif ($request->type == 3) {
            $data = GiftLog::with('receiver')->select([DB::raw('sum(giftPrice) as totalGiftNum'), 'receiver_id'])
                ->groupBy('receiver_id')->where('pk', 1)->whereBetween('created_at', [$startDay, $endDay])
                ->orderByDesc('totalGiftNum')->get();
        } elseif ($request->type == 2) {
            $data =
                GiftLog::where('roomowner_id', '!=', 0)->with(['roomOwner.ownerRoom:id,uid,room_name,room_cover'])->select([DB::raw('sum(giftPrice) as totalGiftNum'), 'roomowner_id'])
                ->groupBy('roomowner_id')->where('pk', 1)->whereBetween('created_at', [$startDay, $endDay])
                ->orderByDesc('totalGiftNum')->get();
        }
        $firstTwentyQueries  = $data->take(20);
        $authenticatedUserId = Auth::user();


        switch ($request['type']) {
            case 1:
                $existsInArray = $firstTwentyQueries->contains('sender_id', $authenticatedUserId->id);
                $dataUser      = $data->where('sender_id', $request->user()->id)->first();
                break;

            case 3:
                $existsInArray = $firstTwentyQueries->contains('receiver_id', $authenticatedUserId->id);
                $dataUser      = $data->where('receiver_id', $request->user()->id)->first();
                break;

            case 2:
                $existsInArray = $firstTwentyQueries->contains('roomowner_id', $authenticatedUserId->id);
                $dataUser      = $data->where('roomowner_id', $request->user()->id)->first();
                break;
        }


        $firstTwentyQueries->transform(function ($gift_log) use ($relation) {
            $gift_log->setRelation('user', $gift_log->{$relation});
            $gift_log->unsetRelation($relation);
            return $gift_log;
        });

        $data = [
            'top'  => PkEventTopResource::collection($firstTwentyQueries),
            'user' => $existsInArray == true ? null : new UserWeeklyStar($authenticatedUserId, $dataUser),
        ];
        return Common::apiResponse(1, '', $data);
    }

    public function topDetails()
    {
        $nowDate = Carbon::now();
        $pkEvent = PkEvent::currentEvent()->with(['rewards'])
            ->first();
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

        if (!$pkEvent) return Common::apiResponse(0, __('there is weekly star now'), null, 422);

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
