<?php

namespace App\Jobs;

use App\Classes\Gifts\SendGiftService;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Helpers\UserCommon;
use App\Helpers\Common;
use App\Models\Cp;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Repositories\Room\RoomTopUsersRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CP\Http\Services\CpService;

class UpdateUserDataWhenSendGift implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $roomTopUsersRepository;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $userId, private int $roomId, private array $receiversIds, private int $giftId, private int $number, private int $price, private ?int $userCoin = null, private $totalNumWin = 0, private $totalUserWin = 0)
    {
        $this->roomTopUsersRepository = new RoomTopUsersRepository();

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        $luckyStatus = Common::getSettingValue('lucky_gifts_action');
        $hostPercentage = 0;
        $receiverFeeRate =null;
        if ($luckyStatus == 1) {
            $version = Common::getSettingValue('lucky_gift_version');
           if (in_array($version, [4])) {
                $receiverFeeRate = \App\Models\FairLuckSetting::getReceiverFeeRate();
            }
            
           $hostPercentage  = $receiverFeeRate ?? getGiftPercentage('host_lucky_gift')  / 10;
        }else {
            $hostPercentage = getGiftPercentage('host_lucky_gift') / 10;
        }

        $room =
            Room::withoutAppends()->where(['id' => $this->roomId])
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone')
                ->with([
                           'owner' => function ($query) {
                               $query->withoutAppends();
                           },
                           'lastPk',
                           'lastPkSession'
                       ])->first(); 
        $gift = Gift::query()->select([
                                          'id', 'name', 'type', 'price'
                                      ])->where('id', $this->giftId)->where('enable', 1)->first();

        $numberOfGift = $this->number * count($this->receiversIds);
        $totalPrice   = $gift->price * $numberOfGift;
        //increase room session
//        $room->enableSaving = false;
//        $room->session      += (int)$totalPrice * 0.1;
//        $room->save();
        $coins = (int)$totalPrice * $hostPercentage;
        if ($this->userCoin != null) {
            UserCommon::UserLuckyGift(0, $this->userId, $gift, ($this->userCoin), $this->number,$this->totalNumWin,$this->totalUserWin);
        }
         $this->updateUserDataWhenSendGift($user, $room, $coins, $this->receiversIds, $gift, $this->number,$hostPercentage);
    }

    private function updateUserDataWhenSendGift( $user, $room, $coins, array $receiversIds, $gift, $number,$hostPercentage)
    {
        $this->updateRoomCoinsToUser($user, $room, $coins);
        $receivedUsers = User::withoutAppends()->with(['agency', 'profile'])->whereIn('id', $receiversIds)->get();

        $price = $number * ($gift->price * $hostPercentage);
        $cpId = Cp::where('user_one_id',  $user->id)->orWhere('user_two_id',  $user->id)->whereIn('status', [1, 4])->first();

        $cpIds = [];
        if ($cpId != null) {
     
            $cpIds = (new CpService())->processCpWhenSendGift($user, $receivedUsers, $gift->id, $price);

        }

        $sendGiftServices = new SendGiftService();
        $pk = (!is_null($room->lastPk) || !is_null($room->lastPkSession)) ? 1 : 0;
        $sendGiftServices->sendGift3ForLuckyGift($number, $room, $gift, $user, $receivedUsers, totalPrice: $price, isPk: $pk, cpIds: $cpIds);

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








