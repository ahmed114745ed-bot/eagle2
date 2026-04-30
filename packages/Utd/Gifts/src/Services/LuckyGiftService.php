<?php

namespace Utd\Gifts\Services;

use App\Contracts\RoomTopUsersRepositoryContract;
use App\Models\User;
use Illuminate\Support\Collection;
use Modules\Charizma\Jobs\UpdateUsersAndSendCharismaToZigo;
use Utd\Gifts\Entities\Gift;
use Utd\Pk\Jobs\UpdatePkAndSendToZigoJob;
use Utd\Room\Entities\Room;

class LuckyGiftService
{
    private $roomTopUsersRepository;

    public function __construct()
    {
        $this->roomTopUsersRepository = app(RoomTopUsersRepositoryContract::class);

    }

    public function updateRoomCoinsToUser($userId, $room, $totalPrice): void
    {
        $topUser = $this->roomTopUsersRepository->findOrCreate($room->id, $userId);
        if ($topUser) {
            $topUser->coins += $totalPrice;
            $topUser->save();
        }
    }

    private function calculateReceiversDiamonds(Collection $jobs)
    {

        //        $usersIds = $jobs->pluck('userId')->toArray();
        $roomIds = $jobs->pluck('roomId')->toArray();
        $giftIds = $jobs->pluck('giftId')->toArray();
        //        $users    = User::withoutAppends()->whereIn('id', $usersIds)->get();
        $rooms = Room::withoutAppends()->whereIn('id', $roomIds)->get();
        $gifts = Gift::query()->whereIn('id', $giftIds)->get();
        foreach ($jobs as $job) {
            $receiverIds = $job->receiverId.'}';
            $receiverIds = unserialize($receiverIds);
            $roomId = $job->roomId;
            $userId = $job->userId;
            $giftId = $job->giftId;
            $number = $job->number;
            $room = $rooms->where('id', $roomId)->first();
            $gift = $gifts->where('id', $giftId)->first();

            if (! $room || ! $gift) {
                continue;
            }
            $numberOfGift = $number * count($receiverIds);
            $totalPrice = $gift->price * $numberOfGift;
            // increase room session
            $room->enableSaving = false;
            $room->session += (int) $totalPrice * 0.1;
            $room->save();
            $coins = (int) $totalPrice * 0.1;

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
        if (! $user) {
            return;
        }
        if ($room->lastPk) {
            dispatch(new UpdatePkAndSendToZigoJob($userId, $room->id, $receiversIds, ($price), $room->microphone))->onQueue('updatePkAndSendToZigo');
        } elseif ($room->charizma_status) {
            dispatch(new UpdateUsersAndSendCharismaToZigo($room, $receiversIds, $price, $userId))->onQueue('default');
        }
        $updateUserWhenSendGift = new UpdateUserWhenSendGift();
        foreach ($receivedUsers as $receivedUser) {
            // Lucky gift code
            $sendGiftServices->sendGift($number, $room, $gift, $user, $receivedUser, totalPrice: $price);
            $updateUserWhenSendGift->updateReceivedLevels($receivedUser);
        }

    }
}
