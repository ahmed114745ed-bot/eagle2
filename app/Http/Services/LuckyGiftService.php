<?php

namespace App\Http\Services;

use App\Jobs\UpdatePkAndSendToZigo;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Classes\Gifts\SendGiftService;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Repositories\Room\RoomTopUsersRepository;
use Modules\Charizma\Jobs\UpdateUsersAndSendCharismaToZigo;

class LuckyGiftService
{
    private $roomTopUsersRepository;

    public function __construct()
    {
        $this->roomTopUsersRepository = new RoomTopUsersRepository();

    }

    public function updateUserInJobs()
    {
        $latestId = DB::table('jobs')->latest()->first()?->id;

        DB::table('jobs')
          ->select(
              DB::raw('COUNT(*) AS COUNT'),
              DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(JSON_EXTRACT(payload, '$.data.command'), '\\\u0000userId\\\\\";i:', -1), ';', 1) AS userId"),
              DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(JSON_EXTRACT(payload, '$.data.command'), '\\\u0000roomId\\\\\";i:', -1), ';', 1) AS roomId"),
              DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(JSON_EXTRACT(payload, '$.data.command'), '\\\u0000giftId\\\\\";i:', -1), ';', 1) AS giftId"),
              DB::raw("SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(JSON_EXTRACT(payload, '$.data.command'), '\\\u0000number\\\\\";i:', -1), ';', 1)) AS number"),
              DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(JSON_EXTRACT(payload, '$.data.command'), 'receiversIds\\\\\";', -1), '}', 1), '}', -1) AS receiverId")
          )
          ->whereIn('queue', ['lucky_gift', 'lucky_gift_2', 'lucky_gift_3'])
          ->when($latestId, function ($q, $latestId) {
              $q->where('id', '<=', $latestId);
          })
          ->groupBy('userId', 'roomId', 'giftId', 'receiverId')
          ->orderBy('userId')
          ->orderBy('roomId')
          ->orderBy('giftId')
          ->orderBy('receiverId')
          ->chunk(500, function ($jobs) {
              $this->calculateReceiversDiamonds($jobs);
          });

        if ($latestId) {
            DB::table('jobs')
              ->whereIn('queue', ['lucky_gift', 'lucky_gift_2', 'lucky_gift_3'])
              ->where('id', '<=', $latestId)->delete();
        }

    }

    private function calculateReceiversDiamonds(Collection $jobs)
    {

        //        $usersIds = $jobs->pluck('userId')->toArray();
        $roomIds  = $jobs->pluck('roomId')->toArray();
        $giftIds  = $jobs->pluck('giftId')->toArray();
        //        $users    = User::withoutAppends()->whereIn('id', $usersIds)->get();
        $rooms    = Room::withoutAppends()->whereIn('id', $roomIds)->get();
        $gifts    = Gift::query()->whereIn('id', $giftIds)->get();
        foreach ($jobs as $job) {
            $receiverIds = $job->receiverId . '}';
            $receiverIds = unserialize($receiverIds);
            $roomId      = $job->roomId;
            $userId      = $job->userId;
            $giftId      = $job->giftId;
            $number      = $job->number;
            $room        = $rooms->where('id', $roomId)->first();
            $gift        = $gifts->where('id', $giftId)->first();

            if(!$room || !$gift) continue;
            $numberOfGift = $number * count($receiverIds);
            $totalPrice   = $gift->price * $numberOfGift;
            //increase room session
            $room->enableSaving = false;
            $room->session      += (int)$totalPrice * 0.1;
            $room->save();
            $coins = (int)$totalPrice * 0.1;

            $this->updateUserDataWhenSendGift($userId, $room, $coins, $receiverIds, $gift, $number);

        }
    }

    private function updateUserDataWhenSendGift(int $userId, $room, $coins, array $receiversIds, $gift, $number)
    {
        $this->updateRoomCoinsToUser($userId, $room, $coins);
        $receivedUsers = User::withoutAppends()->with(['agency', 'profile'])->whereIn('id', $receiversIds)->get();

        $sendGiftServices = new SendGiftService();
        $price = $number * ($gift->price * 0.1);

        $user = User::withoutAppends()->find($userId);
        if (!$user) return;
        if ($room->lastPk) {
            dispatch(new UpdatePkAndSendToZigo($userId, $room->id, $receiversIds, ($price), $room->microphone))->onQueue('updatePkAndSendToZigo');
        }else if ($room->charizma_status){
            dispatch(new UpdateUsersAndSendCharismaToZigo($room, $receiversIds, $price, $userId))->onQueue('default');
        }
        $updateUserWhenSendGift = new UpdateUserWhenSendGift();
        foreach ($receivedUsers as $receivedUser) {
            // Lucky gift code
            $sendGiftServices->sendGift($number, $room, $gift, $user, $receivedUser, totalPrice: $price);
            $updateUserWhenSendGift->updateReceivedLevels($receivedUser);
        }

    }

    /**
     * @param $userId
     * @param $room
     * @param $totalPrice
     * @return void
     */
    public function updateRoomCoinsToUser($userId, $room, $totalPrice): void
    {
        $topUser        = $this->roomTopUsersRepository->findOrCreate($room->id, $userId);
        $topUser->coins += $totalPrice;
        $topUser->save();
    }

}
