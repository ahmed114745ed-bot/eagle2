<?php

namespace App\Tik\Services;


use App\Models\Cp;
use App\Models\User;
use App\Helpers\Common;
use GuzzleHttp\Promise\Utils;
use App\Events\GiftBannerEvent;
use App\Jobs\UpdatePkAndSendToZigo;
use Illuminate\Support\Facades\Log;
use App\Classes\Gifts\SendGiftService;
use Modules\CP\Http\Services\CpService;
use App\Exceptions\NotInfMoneyException;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Tik\Repositories\GiftRepository;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use Modules\CP\Http\Services\CpServices;
use App\Tik\Repositories\GiftLogRepository;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use GuzzleHttp\Exception\BadResponseException;
use App\Repositories\Room\RoomTopUsersRepository;
use Modules\Achievement\Jobs\CalculateAchievement;
use App\Http\Services\RoomAchievementTargetService;
use Modules\Charizma\Jobs\UpdateUsersAndSendCharismaToZigo;

class GiftLogService
{

    public function __construct(
        private readonly GiftRepository $giftRepository,
        private readonly RoomTopUsersRepository $roomTopUsersRepository,
        private readonly RoomRepository $repository,
        private readonly UserRepository $UserRepository,
        private readonly GiftLogRepository $giftLogRepository,
    ) {}


    public function sendGift($request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $data    = $request;
        $user    = $request->user();
        $userId  = $user->id;
        $ownerId = $data['owner_id'];
        $giftId  = $data['id'];
        $number  = $data['num'];

        //get the gift data from id in the parameter
        $gift = $this->giftRepository->findById($giftId);
        // Validation if gift return null
        if (!$gift) return Common::apiResponse(0, 'Gift does not exist or has been removed', null, 404);

        // receivers ids
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);
        $totalPrice = $gift->price * $numberOfGift;
        $totalPriceForOnlyReceiver = $gift->price * $number;

        // if user didn't have inf coins throw exception
        if ($user->di < $totalPrice) return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);

        // Get Room Data
        $room =  $this->repository->findUserRoom($ownerId, 'id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status');
        // Validation if no room
        if (!$room) return Common::apiResponse(0, 'room does not exist', null, 404);

        // validation if this gift vip < user vip then throw Exception
        /** @var User $user*/
        $vip_level = $user->UserVip?->level;
        if (@$vip_level < $gift->vip_level) return Common::apiResponse(0, 'vip ' . $gift->vip_level . ' to send this gift');

        // get received users data
        $receivedUsers = $this->UserRepository->getUsers($receiversIds);

        //        $percentageValues = $this->getReceivedAndSanderPercentage();
        //decrement the user coins
        try {
            $sendPrice = (int)($totalPrice);
            $updateUserWhenSendGift->send($sendPrice, $user);
        } catch (NotInfMoneyException $e) {
            return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);
        }
        //increase room session
        $room->enableSaving = false;
        $room->session      += $totalPrice;
        $room->save();

        //update family level to the sender user


        if (is_array($receiversIds) && count($receiversIds) > 1) {
            $to_id = $receiversIds[0];
            $to    = 'الغرفة';
        } else {
            $to_id = $receiversIds[0];
            $to    = @$receivedUsers->first()->name;
        }


        $fromName = $user->name;
        $sendGiftServices = new SendGiftService();

        $jsonSendGiftData =
            $this->sendToZego($gift, $to_id, $totalPrice, $receiversIds, $room, $to, $ownerId, $number, $user, $receivedUsers->first(), ($request->to_zego == 1 || !$request->has('to_zego')));
        //send to zego if pk not null
        $promises = Common::sendToZego3('SendCustomCommand', $room->id, $userId, $jsonSendGiftData);


        $cpId =  Cp::where('user_one_id',  $user->id)->orWhere('user_two_id',  $user->id)->whereIn('status', [1, 4])->first();
        $cpIds = [];
        //check type of cp
        if ($cpId != null) {
            try {
                $cpIds = (new CpService())->processCpWhenSendGift($user, $receivedUsers, $giftId, $totalPriceForOnlyReceiver);
                // dd($cpIds);
            } catch (\Exception $e) {
                return Common::apiResponse(0, $e->getMessage());
            }
        }

        if ($room->lastPk != null) {

            dispatch(new UpdatePkAndSendToZigo($user->id, $room->id, $receivedUsers->pluck('id')->toArray(), ($gift->price * $number), $room->microphone))->onQueue('updatePk');
        } else if ($room->charizma_status) {
            dispatch(new UpdateUsersAndSendCharismaToZigo($room, $receivedUsers->pluck('id')->toArray(), ($gift->price * $number), $userId))->onQueue('default');
        }



        $realPrice = (int)($number * $gift->price);
        $price = ceil($realPrice);
        $sendGiftServices->sendGift3($number, $room, $gift, $user, $receivedUsers, totalPrice: $price, isPk: @$room->lastPk ? 1 : 0, cpIds: $cpIds);

        foreach ($receivedUsers as $receivedUser) {
            $updateUserWhenSendGift->update($price, $receivedUser);
        }

        $sendGiftServices->updateFamilyLevelForReceiver($receivedUsers, $gift->price * $number);



        if ($room->mode != '1' && $room->mode != '2') {
            $this->updateRoomCoinsToUser($userId, $room, $totalPrice);
            $topUser =
                $this->roomTopUsersRepository->getRoomTopUser($room->id, ['user' => function ($q) {
                    $q->withoutAppends();
                }]);

            $fUser = $topUser?->user;
            if ($room->top_user_id != $userId) {
                $room->top_user_id = $fUser->id;
                $room->save();
                $ms1 = [
                    'messageContent' => [
                        'message'        => 'topSendGifts',
                        'img'            => $fUser?->profile?->avatar,
                        'id'             => $fUser->id,
                        'name'           => $fUser->name,
                        'has_color_name' => Common::hasInPack($fUser->id, 18),
                        'frame'          => Common::getUserDress($fUser->id, $fUser->dress_1, 4, 'img2', true) ?: Common::getUserDress($fUser->id, $fUser->dress_1, 4, 'img1', true),
                        'fid'            => @$fUser->dress_1,
                        'vlev'           => @$fUser->UserVip->level
                    ]
                ];

                $json = json_encode($ms1);

                Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);
            }
        }
        (new RoomAchievementTargetService)->roomTarget($room);

        CalculateAchievement::dispatch($gift, $number, $room->owner)->onQueue('achievement');


        $message = "  {$number} x" . __('api.sendGift') . __("api.value") . "{$gift->price} " .  __('api.to') . "{$to}";
        try {
            Utils::unwrap($promises);
        } catch (BadResponseException $e) {
        }

        if ($totalPrice > 2000) {
            $this->gift_event($gift, $receivedUsers, $user, $totalPrice, $receivedUsers->first(), $receiversIds, $room, $ownerId, $number);
        }



        return Common::apiResponse(1, $message);
    }

    private function gift_event($gift, $receivedUsers, $user, $totalPrice, $receivedUser, $receiversIds, $room, $ownerId, $number)
    {

        $gift_data = [
            'show_gift'         => $gift->show_img ?: $gift->show_img2,
            'gift_img'          => $gift->img,
            'gift_id'           => $gift->id,
            'sender_id'         => (int)$user->id,
            'receiver_id'       => (int)$receivedUser->id,
            'num_gift'          => $totalPrice,
            "plural"            => is_array($receiversIds) && count($receiversIds) > 1,
            'room_session'      => $room->session_string,
            'is_password'       => (bool)(@$room->room_pass),
            'room_id'           => $room->id,
            'room_owner_id'     => $room->uid ?: 0,
            'room_uuid'         => $room->owner?->uuid ?: 0,
            'room_id'           => (string)($room->id ?: 0),
            'room_name'         => $room->room_name ?: '',
            "room_mode"         => $room->mode,
            "room_cover"        => $room->room_cover ?? '',
            "room_background"   => $room->final_room_image ?? '',
            'from_name'         => $user->name,
            'to_name'           => $receivedUser->name,
            'gift_price'        => $gift->price,
            'owner_id'          => $ownerId,
            'number'            => $number,
            'coins'             => $user->coins_string,
            'gift_image_type'   => $gift->image_type,
            's_vip_level'       => @$user->userVip->level ?? 0,
            's_image'           => @$user->profile->avatar ?? '',
            's_name'            => @$user->name ?? '',
            's_sender_level'    => @$user->total_sender_level,
            's_receiver_level'  => @$user->total_received_level,
            'r_vip_level'       => @$receivedUser->userVip->level ?? 0,
            'r_name'            => @$receivedUser->name ?? '',
            'r_image'           => @$receivedUser->profile->avatar ?? '',
            'r_sender_level'    => @$receivedUser->total_received_level,
            'r_receiver_level'  => @$receivedUser->total_sender_level,
        ];

        event(new GiftBannerEvent($gift_data));
    }
    public function sendToZego($gift, $to_id, $totalPrice, $receiversIds, $room, ?string $toName, $ownerId, $number, $user, $firstReceiver, ?bool $isToZigo = false): array
    {

        $zigoData   = collect(
            [
                'show_gift'        => $gift->show_img ?: $gift->show_img2,
                'gift_img'         => $gift->img,
                'gift_id'          => $gift->id,
                'sender_id'        => (int)$user->id,
                'receiver_id'      => (int)$to_id,
                'num_gift'         => $totalPrice,
                "plural"           => is_array($receiversIds) && count($receiversIds) > 1,
                'room_session'     => $room->session_string,
                'is_password'      => (bool)(@$room->room_pass),
                'room_id'          => $room->id,
                'from_name'        => $user->name,
                'to_name'          => $toName,
                'gift_price'       => $gift->price,
                'owner_id'         => $ownerId,
                'number'           => $number,
                'coins'            => $user->coins_string,
                'gift_image_type'            => $gift->image_type,

            ]
        );


        if ($totalPrice >= 2000) {
            $levels     = [
                $user->total_sender_level,
                $user->total_received_level,
                $firstReceiver->total_received_level,
                $firstReceiver->total_sender_level,
            ];
            /*$levels     = Common::getLevels($levels);
            $senderLevels = $levels->where('type', '=',2);
            $receiverLevels = $levels->where('type', '=',1);*/
            $values   = [
                's_vip_level'      => @$user->userVip->level ?? 0,
                's_image'          => @$user->profile->avatar ?? '',
                's_name'           => @$user->name ?? '',
                's_sender_level'   => @$user->total_sender_level,
                's_receiver_level' => @$user->total_received_level,
                'r_vip_level'      => @$firstReceiver->userVip->level ?? 0,
                'r_name'           => @$firstReceiver->name ?? '',
                'r_image'          => @$firstReceiver->profile->avatar ?? '',
                'r_sender_level'   => @$firstReceiver->total_received_level,
                'r_receiver_level' => @$firstReceiver->total_sender_level,
            ];
            $zigoData = $zigoData->merge($values);
        }

        //        dispatch(new SendGiftToZegoJob($zigoData, $totalPrice, ($request->to_zego == 1)))->onQueue('sendGiftToZigo');
        /* $startTime = microtime(true);*/
        return $this->sendZigoGifts($zigoData, $totalPrice, ($isToZigo));
    }

    public function sendZigoGifts($zigoData, $totalPrice, $isToZego): array
    {
        //        Common::sendToZego_2('SendBroadcastMessage', $zigoData['room_id'], $zigoData['sender_id'], $zigoData['from_name'], "  {$zigoData['number']} x ارسل هدية  " . " قيمتها {$zigoData['gift_price']} " . " الى {$zigoData['to_name']}");
        if ($isToZego) {
            $d    = [
                "messageContent" => [
                    "message"     => "showGifts",
                    "showGift"    => $zigoData['show_gift'],
                    'giftImg'     => $zigoData['gift_img'],
                    'gift_id'     => $zigoData['gift_id'],
                    'send_id'     => $zigoData['sender_id'],
                    'receiver_id' => $zigoData['receiver_id'],
                    'isExpensive' => $totalPrice >= 2000,
                    'num_gift'    => $zigoData['number'],
                    "plural"      => $zigoData['plural'],
                    'gift_price'  => $zigoData['room_session'],
                    'giftTP' =>  $totalPrice,
                    'coins'  => @$zigoData['coins'] ?? '0',
                    'type'  => @$zigoData['gift_image_type'] ?? 'mp4',

                ]
            ];
            $json = json_encode($d);
            $jsons[] = $json;
        }

            //            Common::sendToZego('SendCustomCommand', $zigoData['room_id'], $zigoData['sender_id'], $json);
            if ($totalPrice >= 2000) {
                $d     = [
                    "messageContent" => [
                        "msg"     => "SHB",
                        'sv' => $zigoData['s_vip_level'],
                        'si' => $zigoData['s_image'],
                        'sn' => $zigoData['s_name'],
                        'ssl' => $zigoData['s_sender_level'],
                        'srl' => $zigoData['s_receiver_level'],
                        'rv' => $zigoData['r_vip_level'],
                        'rn' => $zigoData['r_name'],
                        'ri' => $zigoData['r_image'],
                        'rsl' => $zigoData['r_sender_level'],
                        'rrl' => $zigoData['r_receiver_level'],
                        'oId'    => (int)$zigoData['owner_id'],
                        'isPass' => $zigoData['is_password'],
                        'GTP' =>  $totalPrice,

                    ]
                ];
                $json  = json_encode($d);

                //                Common::sendToZego('SendCustomCommand', $zigoData['room_id'], $zigoData['sender_id'], $json);

                dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['sender_id'], $zigoData['room_id']), 'heavyProcessing');
            }
        return @$jsons ?? [];
    }

    public function updateRoomCoinsToUser($userId, $room, $totalPrice): void
    {
        $topUser         = $this->roomTopUsersRepository->findOrCreate($room->id, $userId);
        $topUser->coins  += $totalPrice;
        $topUser->save();
    }

    public function userGiftIfo($id, $type, $startDate, $endDate,$perPage, $page)
    {
        return $this->giftLogRepository->userGiftInfo($id, $type, $startDate, $endDate,$perPage, $page);
    }
}
