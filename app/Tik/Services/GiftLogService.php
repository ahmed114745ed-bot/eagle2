<?php

namespace App\Tik\Services;


use App\Enums\GiftSourceType;
use App\Enums\UserCoinLogType;
use App\Helpers\CacheHelper;
use App\Helpers\UserCoinLogHelper;
use App\Models\Cp;
use App\Models\User;
use App\Helpers\Common;
use App\Models\UserGift;
use App\Tik\DTO\ReceiverGiftDTO;
use Carbon\Carbon;
use GuzzleHttp\Promise\Utils;
use App\Events\GiftBannerEvent;
use Utd\Room\Jobs\UpdatePkAndSendToZigoJob;
use App\Classes\Gifts\SendGiftService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Charizma\Jobs\UpdateSendCharismaToZigo;
use Modules\CP\Http\Services\CpService;
use App\Tik\Repositories\GiftRepository;
use App\Contracts\RoomRepositoryContract;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\GiftLogRepository;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use GuzzleHttp\Exception\BadResponseException;
use App\Contracts\RoomTopUsersRepositoryContract;
use Modules\RoomBoom\Services\NewRoomBoomGiftService;


class GiftLogService
{

    public function __construct(
        private readonly GiftRepository $giftRepository,
        private readonly RoomTopUsersRepositoryContract $roomTopUsersRepository,
        private readonly RoomRepositoryContract $repository,
        private readonly UserRepository $UserRepository,
        private readonly GiftLogRepository $giftLogRepository,
    ) {}


    /**
     * @throws \Throwable
     */
    public function sendGift($request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        return DB::transaction(function () use ($request, $updateUserWhenSendGift) {

            // room_id 1
            // owner id 1
            $data    = $request;
            $user    = $request->user();
            $userId  = $user->id;
            $ownerId = @$data['owner_id'];
            $roomId = @$data['room_id'];
            $giftId  = $data['id'];
            $number  = $data['num'];
            $type  = $data['type'];
            $sourceType = GiftSourceType::fromType($type)->value;


            //get the gift data from id in the parameter
            $gift = $this->giftRepository->findById($giftId);
            // Validation if gift return null
            if (!$gift) return  throw new \Exception('Gift does not exist or has been removed');

            // receivers ids
            $receiversIds = explode(',', $data['toUid']);
            $numberOfGift = $number * count($receiversIds);
            $totalPrice = $gift->price * $numberOfGift;
            $totalPriceForOnlyReceiver = $gift->price * $number;
            // if user didn't have inf coins throw exception
            $check = $this->checkGiftAvailability($user, $gift, $number, $type, $totalPrice);
            if ($check) {
                return $check;
            }

            // Get Room Data
            if (isset($ownerId)){
                $room =  $this->repository->findTypeUserRoom($ownerId, selectRow: 'id,uid,room_visitor,	room_name,room_cover,play_num,hot,room_pass,session,microphone,charizma_status,type');

            }else{
                $room =  $this->repository->findUserRoomById($roomId, 'id,uid,room_visitor,play_num,room_cover,	room_name,hot,room_pass,session,microphone,charizma_status,type');
                $ownerId = $room?->uid;
            }

            // Validation if no room
            if (!$room)  throw new \Exception('room does not exist');

            // validation if this gift vip < user vip then throw Exception
            /** @var User $user*/
            $vip_level = $user->UserVip?->level;
            if (@$vip_level < $gift->vip_level) throw new \Exception('vip ' . $gift->vip_level . ' to send this gift');

            // get received users data
            $receivedUsers = $this->UserRepository->getUsers($receiversIds);

            //        $percentageValues = $this->getReceivedAndSanderPercentage();
            //decrement the user coins
            $sendPrice = (int)($totalPrice);
            if ($type !== 'bag') {
                $amountBefore = $user->di;

                UserCoinLogHelper::logByType(
                    $user->id,
                    -abs($sendPrice),
                    $amountBefore,
                    UserCoinLogType::GIFT,
                    $gift?->name
                );

                $updateUserWhenSendGift->send($sendPrice, $user);
            } else {

                $updateUserWhenSendGift->sendFromBagAndRemoveGift($sendPrice, $user, $giftId, $number);
            }

            //increase room session
            $room->enableSaving = false;
            $room->session      += $totalPrice;
            $room->save();

            //update family level to the sender user

            if (is_array($receiversIds) && count($receiversIds) > 1) {
                $to_id = $receiversIds[0];
                $to ="";
                if($room->type == "audio"){
                    $to    = 'الغرفة';
                }else{
                    $to    = __('live');
                }

            } else {
                $to_id = $receiversIds[0];
                $to    = @$receivedUsers->first()->name;
            }

            $fromName = $user->name;
            $sendGiftServices = new SendGiftService();


            $cpId =  Cp::where(function ($query) use ($user) {
                $query->where('user_one_id',  $user->id)->orWhere('user_two_id',  $user->id);
            })->whereIn('status', [1, 4])->first();
           
            $cpIds = [];
            $cpEnableAllGifts = getCpGiftsStatus('cp_enable_all_gifts') ?? 1;


            if ($cpId != null) {
                 if ($cpEnableAllGifts || ($gift->category && $gift->category->type === 'cp')) {
                    try {

                        $cpIds = (new CpService())->processCpWhenSendGift($user, $receivedUsers, $giftId, $totalPriceForOnlyReceiver);
                    } catch (\Exception $e) {

                    }
                }
            }

            if ($room->lastPk != null) {

                dispatch(new UpdatePkAndSendToZigoJob($user->id, $room->id, $receivedUsers->pluck('id')->toArray(), ($gift->price * $number), $room))
                    ->afterCommit()
                    ->onQueue('updatePk');
            }

            if ($room->charizma_status) {
                dispatch(new UpdateSendCharismaToZigo($room->id, $receivedUsers->pluck('id')->toArray(), ($gift->price * $number), $userId))
                    ->afterCommit()
                    ->onQueue('default');
            }

            $realPrice = (int)($number * $gift->price);

            $price = ceil($realPrice);

            $roomBoomUuid = $sendGiftServices->sendGift3($number, $room, $gift, $user, $receivedUsers, totalPrice: $price, isPk: @$room->lastPk ? 1 : 0, cpIds: $cpIds, sourceType: $sourceType, type: $type);

//            (new RoomBoomGiftService())->sendGift($room, $totalPrice, $roomBoomUuid);
            $settings = CacheHelper::cacheSettings();
            /** @var Collection $rememberForever*/
            if (gettype($settings) !== 'array'){
                $settings = $settings->pluck('value', 'key')->toArray();
            }

            $roomBoomSettings = $settings['room_boom'] ?? 1;
            if ($roomBoomSettings){
                (new NewRoomBoomGiftService())->sendGift($room, $totalPrice, $userId);
            } else {
                $tz = getTimezone();
                $todayStart = Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');

                $totalRoomGift = (new NewRoomBoomGiftService())->getOrCreateTotalRoomGift($room->id, $todayStart);

                $totalRoomGift->increment('current_total', $totalPrice);
            }

            foreach ($receivedUsers as $receivedUser) {
                $updateUserWhenSendGift->update($price, $receivedUser);
            }

            $sendGiftServices->updateFamilyLevelForReceiver($receivedUsers, $gift->price * $number);

            if ($room->mode != '1' && $room->mode != '2') {
                $this->updateRoomCoinsToUser($userId, $room, $totalPrice);
                /*$topUser =
                    $this->roomTopUsersRepository->getRoomTopUser($room->id, ['user' => function ($q) {
                        $q->withoutAppends();
                    }]);*/

                /*  $fUser = $topUser?->user;
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
                  }*/
            }
            // (new RoomAchievementTargetService)->roomTarget($room);

            // CalculateAchievement::dispatch($gift, $number, $room->owner)->onQueue('achievement');



                $message = "  {$numberOfGift} x" . __('api.sendGift') . __("api.value") . "{$totalPrice} " .  __('api.to') . "{$to}";



            $totalGiftPrice = Common::getConfig('total_gift_price') ?? 2000;


            if ($totalPrice >= $totalGiftPrice) {
                try {
                    $gift_data = $this->giftEvent($gift, $user, $totalPrice, $receivedUsers->first(), $receiversIds, $room, $number);
                    event(new GiftBannerEvent($gift_data));
                } catch (\Exception $e) {

                }

            }

            return $message;
        });
    }


    public function sendTestGift($request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        return DB::transaction(function () use ($request, $updateUserWhenSendGift) {

            $data    = $request;
            $user    = User::orderByDesc('di')->first();
            $userId  = $user->id;
            $ownerId = $data['owner_id'];
            $giftId  = $data['id'];
            $number  = $data['num'];
            $type  = $data['type'];
            $sourceType = GiftSourceType::fromType($type)->value;


            //get the gift data from id in the parameter
            $gift = $this->giftRepository->findById($giftId);
            // Validation if gift return null
            if (!$gift) return  throw new \Exception('Gift does not exist or has been removed');

            // receivers ids
            $receiversIds = explode(',', $data['toUid']);
            $numberOfGift = $number * count($receiversIds);
            $totalPrice = $gift->price * $numberOfGift;
            $totalPriceForOnlyReceiver = $gift->price * $number;
            // if user didn't have inf coins throw exception
            $check = $this->checkGiftAvailability($user, $gift, $number, $type, $totalPrice);
            if ($check) {
                return $check;
            }


            // Get Room Data
            $room =  $this->repository->findUserRoom($ownerId, 'id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status');
            // Validation if no room
            if (!$room)  throw new \Exception('room does not exist');

            // validation if this gift vip < user vip then throw Exception
            /** @var User $user*/
            $vip_level = $user->UserVip?->level;
            if (@$vip_level < $gift->vip_level) throw new \Exception('vip ' . $gift->vip_level . ' to send this gift');

            // get received users data
            $receivedUsers = $this->UserRepository->getUsers($receiversIds);

            //        $percentageValues = $this->getReceivedAndSanderPercentage();
            //decrement the user coins
            $sendPrice = (int)($totalPrice);
            if ($type !== 'bag') {
                $amountBefore = $user->di;

                UserCoinLogHelper::logByType(
                    $user->id,
                    -abs($sendPrice),
                    $amountBefore,
                    UserCoinLogType::GIFT,
                    $gift?->name
                );

                $updateUserWhenSendGift->send($sendPrice, $user);
            } else {

                $updateUserWhenSendGift->sendFromBagAndRemoveGift($sendPrice, $user, $giftId, $number);
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

            $cpId =  Cp::where(function ($query) use ($user) {
                $query->where('user_one_id',  $user->id)->orWhere('user_two_id',  $user->id);
            })->whereIn('status', [1, 4])->first();
            $cpIds = [];
            //check type of cp
            if ($cpId != null) {
                try {
                    $cpIds = (new CpService())->processCpWhenSendGift($user, $receivedUsers, $giftId, $totalPriceForOnlyReceiver);
                    // dd($cpIds);
                } catch (\Exception $e) {

                }
            }

            if ($room->lastPk != null) {

                dispatch(new UpdatePkAndSendToZigoJob($user->id, $room->id, $receivedUsers->pluck('id')->toArray(), ($gift->price * $number), $room->microphone))->onQueue('updatePk');
            }

            if ($room->charizma_status) {
                dispatch(new UpdateSendCharismaToZigo($room->id, $receivedUsers->pluck('id')->toArray(), ($gift->price * $number), $userId))->onQueue('default');
            }

            $realPrice = (int)($number * $gift->price);

            $price = ceil($realPrice);

            $roomBoomUuid = $sendGiftServices->sendGift3($number, $room, $gift, $user, $receivedUsers, totalPrice: $price, isPk: @$room->lastPk ? 1 : 0, cpIds: $cpIds, sourceType: $sourceType);

//            (new RoomBoomGiftService())->sendGift($room, $totalPrice, $roomBoomUuid);
            (new NewRoomBoomGiftService())->sendGift($room, $totalPrice, $userId);

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
            // (new RoomAchievementTargetService)->roomTarget($room);

            // CalculateAchievement::dispatch($gift, $number, $room->owner)->onQueue('achievement');

            $message = "  {$numberOfGift} x" . __('api.sendGift') . __("api.value") . "{$totalPrice} " .  __('api.to') . "{$to}";
            try {
                Utils::unwrap($promises);
            } catch (BadResponseException $e) {
            }

            $totalGiftPrice = Common::getConfig('total_gift_price') ?? 2000;

            if ($totalPrice > $totalGiftPrice) {
                $this->gift_event($gift, $receivedUsers, $user, $totalPrice, $receivedUsers->first(), $receiversIds, $room, $ownerId, $number);
            }
            return $message;
        });
    }

    /**
     * @throws \Throwable
     */
    private function checkGiftAvailability($user, $gift, $number, $type, $totalPrice)
    {
        if ($type == 'bag') {

            $existingGiftCount = UserGift::where('user_id', $user->id)
                ->where('gift_id', $gift->id)
                ->where(function ($query) {
                    $query->where('expire', 0)
                        ->orWhereRaw('DATE_ADD(created_at, INTERVAL expire DAY) >= NOW()');
                })->first();


            throw_if((!$existingGiftCount || $existingGiftCount->quantity < $number), \Exception::class, 'Receiver has reached maximum allowed gifts');


            return null;
        }

        throw_if(
            $user->di < $totalPrice,
            \Exception::class,
            'Insufficient balance, please go to recharge!'
        );

        return null;
    }

    private function gift_event($gift, $receivedUsers, $user, $totalPrice, $receivedUser, $receiversIds, $room, $ownerId, $number)
    {
        $gift_data = [
            'show_gift'         => $gift->show_img ?: $gift->show_img2,
            'gift_img'          => $gift->img,
            'gift_id'           => $gift->id,
            'sender_id'         => (int)$user->id,
            'receiver_id'       => @(int)$receivedUser->id,
            'num_gift'          => $totalPrice,
            "plural"            => is_array($receiversIds) && count($receiversIds) > 1,
            'room_session'      => $room->session_string,
            'is_password'       => (bool)(@$room->room_pass),
            'room_uuid'         => $room->owner?->uuid ?: 0,
            'room_id'           => (string)($room->id ?: 0),
            'room_owner_id'     => $room->uid ?: 0,
            'room_name'         => $room->room_name ?: '',
            "room_mode"         => $room->mode,
            "room_cover"        => $room->room_cover ?? '',
            "room_background"   => $room->final_room_image ?? '',
            'from_name'         => $user->name,
            'to_name'           => @$receivedUser->name,
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
            'room_type'  => @$room->type,
        ];

        event(new GiftBannerEvent($gift_data));
    }


    public function giftEvent($gift, $user, $totalPrice, $receivedUser, $receiversIds, $room, $number)
    {

        $receiverGiftDTO = (count($receiversIds) > 1)? ReceiverGiftDTO::fromRoom($room) : ReceiverGiftDTO::fromUser($receivedUser);
        $gift_data = [
            'show_gift'         => $gift->show_img ?: $gift->show_img2,
            'gift_img'          => $gift->img,
            'gift_id'           => $gift->id,
            'sender_id'         => (int)$user->id,
            'receiver_id'       => $receiverGiftDTO->id,
            'num_gift'          => $totalPrice,
            "plural"            => is_array($receiversIds) && count($receiversIds) > 1,
            'room_session'      => $room->session_string,
            'is_password'       => (bool)(@$room->room_pass),
            'room_uuid'         => $room->owner?->uuid ?: 0,
            'room_id'           => (string)($room->id ?: 0),
            'room_owner_id'     => $room->uid ?: 0,
            'room_name'         => $room->room_name ?: '',
            "room_mode"         => $room->mode,
            "room_cover"        => $room->room_cover ?? '',
            "room_background"   => $room->final_room_image ?? '',
            'from_name'         => $user->name,
            'to_name'           => $receiverGiftDTO->name,
            'gift_price'        => $gift->price,
            'owner_id'          => $room->uid,
            'number'            => $number,
            'coins'             => $user->coins_string,
            'gift_image_type'   => $gift->image_type,
            's_vip_level'       => @$user->userVip->level ?? 0,
            's_image'           => @$user->profile->avatar ?? '',
            's_name'            => @$user->name ?? '',
            's_sender_level'    => @$user->total_sender_level,
            's_receiver_level'  => @$user->total_received_level,
            'r_vip_level'       => $receiverGiftDTO->vipLevel ,
            'r_name'            => $receiverGiftDTO->name ?? '',
            'r_image'           => $receiverGiftDTO->avatar ?? '',
            'r_sender_level'    => $receiverGiftDTO->senderLevel,
            'r_receiver_level'  => $receiverGiftDTO->receiverLevel,
            'room_type'  => @$room->type,
        ];

        return $gift_data;
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
                'gift_price'       => $totalPrice,
                'owner_id'         => $ownerId,
                'number'           => $number,
                'coins'            => $user->coins_string,
                'gift_image_type'            => $gift->image_type,
                'room_type'            => $room->type ?? 'audio',

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
                    'gift_price'  => $totalPrice, // $zigoData['room_session'],,
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

            //                dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['sender_id'], $zigoData['room_id']), 'heavyProcessing');
        }
        return @$jsons ?? [];
    }

    public function updateRoomCoinsToUser($userId, $room, $totalPrice): void
    {
        $topUser = $this->roomTopUsersRepository->findOrCreate($room->id, $userId);
        if ($topUser) {
            $topUser->coins += $totalPrice;
            $topUser->save();
        }
    }

    public function userGiftIfo($id, $type, $startDate, $endDate, $perPage, $page)
    {
        return $this->giftLogRepository->userGiftInfo($id, $type, $startDate, $endDate, $perPage, $page);
    }
}
