<?php

namespace App\Jobs;

use App\Classes\Gifts\SendGiftService;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Helpers\UserCommon;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Repositories\Room\RoomTopUsersRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUserDataWhenSendGift implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $roomTopUsersRepository;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $userId, private int $roomId, private array $receiversIds, private int $giftId, private int $number, private int $price, private ?int $userCoin = null,private $totalNumWin,private $totalUserWin)
    {
        $this->roomTopUsersRepository = new RoomTopUsersRepository();

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::Find($this->userId);
        $room =
            Room::withoutAppends()->where(['id' => $this->roomId])
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone')
                ->with([
                           'owner' => function ($query) {
                               $query->withoutAppends();
                           }
                       ])->first();
        $gift = Gift::query()->select([
                                          'id', 'name', 'type', 'price'
                                      ])->where('type', 6)->where('id', $this->giftId)->where('enable', 1)->first();

        $numberOfGift = $this->number * count($this->receiversIds);
        $totalPrice   = $gift->price * $numberOfGift;
        //increase room session
//        $room->enableSaving = false;
//        $room->session      += (int)$totalPrice * 0.1;
//        $room->save();
        $coins = (int)$totalPrice * 0.1;
        if ($this->userCoin != null) {
            UserCommon::UserLuckyGift(0, $this->userId, $gift, ($this->userCoin), $this->number,$this->totalNumWin,$this->totalUserWin);
        }
        $this->updateUserDataWhenSendGift($user, $room, $coins, $this->receiversIds, $gift, $this->number);
    }

    private function updateUserDataWhenSendGift( $user, $room, $coins, array $receiversIds, $gift, $number)
    {
        $this->updateRoomCoinsToUser($user, $room, $coins);
        $receivedUsers = User::withoutAppends()->with(['agency', 'profile'])->whereIn('id', $receiversIds)->get();

        $price = $number * ($gift->price * 0.1);

        $sendGiftServices = new SendGiftService();
        $sendGiftServices->sendGift2($number, $room, $gift, $user, $receivedUsers, totalPrice: $price, isPk: @$room->lastPk ? 1 : 0);

    }

    /**
     * @param $userId
     * @param $room
     * @param $totalPrice
     * @return void
     */
    public function updateRoomCoinsToUser( $user, $room, $totalPrice): void
    {
        $topUser        = $this->roomTopUsersRepository->findOrCreate($room->id, $user->id);
        $topUser->coins += $totalPrice;
        $topUser->save();
    }
}
