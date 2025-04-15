<?php

namespace App\Classes\Room;

use App\Exceptions\NotInfCoins;
use App\Helpers\Common;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Jobs\SendCustomToZend;
use App\Models\Room;
use App\Models\User;
use App\Repositories\Room\RoomRepoInterface;
use Illuminate\Validation\ValidationException;

class RoomComments
{

    private $roomRepo;

    public function __construct(RoomRepoInterface $roomRepo)
    {
        $this->roomRepo = $roomRepo;
    }

    /**
     *
     * @throws NotInfCoins
     */
    public function sendComments(User $user, array $data)
    {
        // configuration to number of coins required
        $commentCoinsValue = Common::getConf('special_bar_coin') ?? 50;

        if ($this->checkUserCommentCoins($user->di, $commentCoinsValue)) {
            $room = Room::query()->find($data['room_id']);
            $ms = [
                'messageContent' => [
                    'msg' => 'yellowBanner',
                    'uId' => $user->id,
                    'umsg' => $data['message'],
                    'oid' => @$room->uid,
                    'ps' => @$room->room_pass != null || @$room->room_pass != '', // password_status
                    'room' => [
                        'id' => @$room->id ?? 0,
                        'name' => @$room->room_name ?? '',
                        'cover' => @$room->room_cover ?? '',
                        'background' => @$room->final_room_image ?? '',
                        'mode' => @$room->mode ?? 0,
                        'stream_type' => @$room->is_live ?? false,
                        'gift_price' => @$room->gifts->sum('giftPrice'),
                        'owner' => [
                            'id' => @$room->owner->id ?? 0,
                            'uuid' => @$room->owner->uuid ?? 0,
                        ],
                    ],
                ]
            ];
            $json = json_encode($ms);


            Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);

            dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $user->id, $data['room_id']), 'heavyProcessing');

            //send comment in queue
            //            dispatch(new SendCustomToZend($user->id, $data['room_id'], $data['message'], $rooms))->onQueue('sendComment');
            // minus coins for comments
            $user = $this->minusUserCoins($user, $commentCoinsValue);

            $user->save();
        } else {
            throw new NotInfCoins('Not inf coins');
        }
    }

    private function checkUserCommentCoins(int $totalCoins, int $coinsForComment): bool
    {
        return $totalCoins >= $coinsForComment;
    }

    private function minusUserCoins(User $user, int $numOfCoins)
    {
        $user->di -= $numOfCoins;
        return $user;
    }
}
