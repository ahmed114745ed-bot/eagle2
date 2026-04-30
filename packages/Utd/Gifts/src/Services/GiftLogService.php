<?php

namespace Utd\Gifts\Services;

use App\Contracts\RoomRepositoryContract;
use App\Contracts\RoomTopUsersRepositoryContract;
use App\Contracts\UserRepositoryContract;
use App\Enums\UserCoinLogType;
use App\Helpers\CacheHelper;
use App\Helpers\Common;
use App\Helpers\UserCoinLogHelper;
use App\Models\Cp;
use App\Models\User;
use App\Support\PackageHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Charizma\Jobs\UpdateSendCharismaToZigo;
use Modules\CP\Http\Services\CpService;
use Modules\RoomBoom\Services\NewRoomBoomGiftService;
use Throwable;
use Utd\Agency\Repositories\UserRepository as AgencyUserRepository;
use Utd\Gifts\DTOs\ReceiverGiftDTO;
use Utd\Gifts\Entities\UserGift;
use Utd\Gifts\Enums\GiftSourceType;
use Utd\Gifts\Events\GiftBannerEvent;
use Utd\Gifts\Repositories\GiftLogRepository;
use Utd\Gifts\Repositories\GiftRepository;
use Utd\Room\Jobs\UpdatePkAndSendToZigoJob;
use Utd\Room\Repositories\RoomRepoInterface;

class GiftLogService
{
    public function __construct(
        private readonly GiftRepository $giftRepository,
        private readonly GiftLogRepository $giftLogRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function sendGift($request, $updateUserWhenSendGift = null)
    {
        return DB::transaction(function () use ($request, $updateUserWhenSendGift) {

            $data = $request;
            $user = $request->user();
            $userId = $user->id;
            $ownerId = @$data['owner_id'];
            $roomId = @$data['room_id'];
            $giftId = $data['id'];
            $number = $data['num'];
            $type = $data['type'];
            $giftSourceType = $this->getGiftSourceTypeClass();
            $sourceType = $giftSourceType ? $giftSourceType::fromType($type)->value : 'default';

            // get the gift data from id in the parameter
            $gift = $this->giftRepository->findById($giftId);
            // Validation if gift return null
            if (! $gift) {
                return throw new Exception('Gift does not exist or has been removed');
            }

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
            $repository = $this->getRoomRepository();
            if (! $repository) {
                throw new Exception('Room repository not available');
            }

            if (isset($ownerId)) {
                $room = $repository->findTypeUserRoom($ownerId, selectRow: 'id,uid,room_visitor,	room_name,room_cover,play_num,hot,room_pass,session,microphone,charizma_status,type');

            } else {
                $room = $repository->findUserRoomById($roomId, 'id,uid,room_visitor,play_num,room_cover,	room_name,hot,room_pass,session,microphone,charizma_status,type');
                $ownerId = $room?->uid;
            }

            // Validation if no room
            if (! $room) {
                throw new Exception('room does not exist');
            }

            // validation if this gift vip < user vip then throw Exception
            $vip_level = $user->UserVip?->level;
            if (@$vip_level < $gift->vip_level) {
                throw new Exception('vip '.$gift->vip_level.' to send this gift');
            }

            // get received users data
            $userRepository = $this->getUserRepository();
            if (! $userRepository) {
                throw new Exception('User repository not available');
            }
            $receivedUsers = $userRepository->getUsers($receiversIds);

            //        $percentageValues = $this->getReceivedAndSanderPercentage();
            // decrement the user coins
            $sendPrice = (int) ($totalPrice);
            if ($type !== 'bag') {
                $amountBefore = $user->di;

                $userCoinLogHelper = $this->getUserCoinLogHelperClass();
                $userCoinLogType = $this->getUserCoinLogTypeClass();
                if ($userCoinLogHelper && $userCoinLogType) {
                    $userCoinLogHelper::logByType(
                        $user->id,
                        -abs($sendPrice),
                        $amountBefore,
                        $userCoinLogType::GIFT,
                        $gift?->name
                    );
                }

                if ($updateUserWhenSendGift) {
                    $updateUserWhenSendGift->send($sendPrice, $user);
                }
            } else {

                if ($updateUserWhenSendGift) {
                    $updateUserWhenSendGift->sendFromBagAndRemoveGift($sendPrice, $user, $giftId, $number);
                }
            }
            $room->session += $totalPrice;
            $room->save();

            // update family level to the sender user

            if (is_array($receiversIds) && count($receiversIds) > 1) {
                $to_id = $receiversIds[0];
                $to = '';
                if ($room->type === 'audio') {
                    $to = 'الغرفة';
                } else {
                    $to = __('live');
                }

            } else {
                $to_id = $receiversIds[0];
                $to = @$receivedUsers->first()->name;
            }

            $fromName = $user->name;
            $sendGiftServices = $this->getSendGiftServiceInstance();
            if (! $sendGiftServices) {
                throw new Exception('SendGiftService not available');
            }

            $cpModel = $this->getCpModelClass();
            $cpId = null;
            if ($cpModel) {
                $cpId = $cpModel::where(function ($query) use ($user) {
                    $query->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id);
                })->whereIn('status', [1, 4])->first();
            }

            $cpIds = [];
            $cpEnableAllGifts = getCpGiftsStatus('cp_enable_all_gifts') ?? 1;

            if ($cpId !== null) {
                if ($cpEnableAllGifts || ($gift->category && $gift->category->type === 'cp')) {
                    try {

                        if ($this->hasCpService()) {
                            $cpService = $this->getCpService();
                            $cpIds = $cpService->processCpWhenSendGift($user, $receivedUsers, $giftId, $totalPriceForOnlyReceiver);
                        }
                    } catch (Exception $e) {

                    }
                }
            }

            if ($room->lastPk !== null) {
                $updatePkJobClass = $this->getUpdatePkJobClass();
                if ($updatePkJobClass) {
                    dispatch(new $updatePkJobClass($user->id, $room->id, $receivedUsers->pluck('id')->toArray(), ($gift->price * $number), $room))
                        ->afterCommit()
                        ->onQueue('updatePk');
                }
            }

            if ($room->charizma_status) {
                $charizmaJob = $this->getCharizmaJob($room->id, $receivedUsers->pluck('id')->toArray(), ($gift->price * $number), $userId);
                if ($charizmaJob) {
                    dispatch($charizmaJob)
                        ->afterCommit()
                        ->onQueue('default');
                }
            }

            $realPrice = (int) ($number * $gift->price);

            $price = ceil($realPrice);

            $roomBoomUuid = $sendGiftServices->sendGift3($number, $room, $gift, $user, $receivedUsers, totalPrice: $price, isPk: @$room->lastPk ? 1 : 0, cpIds: $cpIds, sourceType: $sourceType, type: $type);

            //            (new RoomBoomGiftService())->sendGift($room, $totalPrice, $roomBoomUuid);
            $cacheHelper = $this->getCacheHelperClass();
            $settings = $cacheHelper ? $cacheHelper::cacheSettings() : [];
            /** @var Collection $rememberForever */
            if (gettype($settings) !== 'array') {
                $settings = $settings->pluck('value', 'key')->toArray();
            }

            $roomBoomSettings = $settings['room_boom'] ?? 1;
            if ($roomBoomSettings) {
                if ($this->hasRoomBoomService()) {
                    $roomBoomService = $this->getRoomBoomService();
                    $roomBoomService->sendGift($room, $totalPrice, $userId);
                }
            } else {
                $tz = getTimezone();
                $todayStart = Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');

                $roomBoomService = $this->getRoomBoomService();
                $totalRoomGift = $roomBoomService ? $roomBoomService->getOrCreateTotalRoomGift($room->id, $todayStart) : null;

                $totalRoomGift->increment('current_total', $totalPrice);
            }

            foreach ($receivedUsers as $receivedUser) {
                $updateUserWhenSendGift->update($price, $receivedUser);
            }

            $sendGiftServices->updateFamilyLevelForReceiver($receivedUsers, $gift->price * $number);

            if ($room->mode !== '1' && $room->mode !== '2') {
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

            $message = "  {$numberOfGift} x".__('api.sendGift').__('api.value')."{$totalPrice} ".__('api.to')."{$to}";

            $commonHelper = $this->getCommonHelperClass();
            $totalGiftPrice = $commonHelper ? $commonHelper::getConfig('total_gift_price') : 2000;
            $commonHelper = $this->getCommonHelperClass();
            $totalGiftPrice = $commonHelper ? $commonHelper::getConfig('total_gift_price') : 2000;
            $totalGiftPrice = $totalGiftPrice ?? 2000;

            if ($totalPrice >= $totalGiftPrice) {
                try {
                    $gift_data = $this->giftEvent($gift, $user, $totalPrice, $receivedUsers->first(), $receiversIds, $room, $number);
                    $this->fireGiftBannerEvent($gift_data);
                } catch (Exception $e) {

                }

            }

            return $message;
        });
    }

    public function giftEvent($gift, $user, $totalPrice, $receivedUser, $receiversIds, $room, $number)
    {

        $receiverGiftDTOClass = $this->getReceiverGiftDTOClass();
        $receiverGiftDTO = null;
        if ($receiverGiftDTOClass) {
            $receiverGiftDTO = (count($receiversIds) > 1) ? $receiverGiftDTOClass::fromRoom($room) : $receiverGiftDTOClass::fromUser($receivedUser);
        }
        $gift_data = [
            'show_gift' => $gift->show_img ?: $gift->show_img2,
            'gift_img' => $gift->img,
            'gift_id' => $gift->id,
            'sender_id' => (int) $user->id,
            'receiver_id' => $receiverGiftDTO->id,
            'num_gift' => $totalPrice,
            'plural' => is_array($receiversIds) && count($receiversIds) > 1,
            'room_session' => $room->session_string,
            'is_password' => (bool) (@$room->room_pass),
            'room_uuid' => $room->owner?->uuid ?: 0,
            'room_id' => (string) ($room->id ?: 0),
            'room_owner_id' => $room->uid ?: 0,
            'room_name' => $room->room_name ?: '',
            'room_mode' => $room->mode,
            'room_cover' => $room->room_cover ?? '',
            'room_background' => $room->final_room_image ?? '',
            'from_name' => $user->name,
            'to_name' => $receiverGiftDTO->name,
            'gift_price' => $gift->price,
            'owner_id' => $room->uid,
            'number' => $number,
            'coins' => $user->coins_string,
            'gift_image_type' => $gift->image_type,
            's_vip_level' => @$user->userVip->level ?? 0,
            's_image' => @$user->profile->avatar ?? '',
            's_name' => @$user->name ?? '',
            's_sender_level' => @$user->total_sender_level,
            's_receiver_level' => @$user->total_received_level,
            'r_vip_level' => $receiverGiftDTO->vipLevel,
            'r_name' => $receiverGiftDTO->name ?? '',
            'r_image' => $receiverGiftDTO->avatar ?? '',
            'r_sender_level' => $receiverGiftDTO->senderLevel,
            'r_receiver_level' => $receiverGiftDTO->receiverLevel,
            'room_type' => @$room->type,
        ];

        return $gift_data;
    }

    public function sendToZego($gift, $to_id, $totalPrice, $receiversIds, $room, ?string $toName, $ownerId, $number, $user, $firstReceiver, ?bool $isToZigo = false): array
    {
        $zigoData = collect(
            [
                'show_gift' => $gift->show_img ?: $gift->show_img2,
                'gift_img' => $gift->img,
                'gift_id' => $gift->id,
                'sender_id' => (int) $user->id,
                'receiver_id' => (int) $to_id,
                'num_gift' => $totalPrice,
                'plural' => is_array($receiversIds) && count($receiversIds) > 1,
                'room_session' => $room->session_string,
                'is_password' => (bool) (@$room->room_pass),
                'room_id' => $room->id,
                'from_name' => $user->name,
                'to_name' => $toName,
                'gift_price' => $totalPrice,
                'owner_id' => $ownerId,
                'number' => $number,
                'coins' => $user->coins_string,
                'gift_image_type' => $gift->image_type,
                'room_type' => $room->type ?? 'audio',

            ]
        );

        if ($totalPrice >= 2000) {
            $levels = [
                $user->total_sender_level,
                $user->total_received_level,
                $firstReceiver->total_received_level,
                $firstReceiver->total_sender_level,
            ];
            /*$levels     = Common::getLevels($levels);
            $senderLevels = $levels->where('type', '=',2);
            $receiverLevels = $levels->where('type', '=',1);*/
            $values = [
                's_vip_level' => @$user->userVip->level ?? 0,
                's_image' => @$user->profile->avatar ?? '',
                's_name' => @$user->name ?? '',
                's_sender_level' => @$user->total_sender_level,
                's_receiver_level' => @$user->total_received_level,
                'r_vip_level' => @$firstReceiver->userVip->level ?? 0,
                'r_name' => @$firstReceiver->name ?? '',
                'r_image' => @$firstReceiver->profile->avatar ?? '',
                'r_sender_level' => @$firstReceiver->total_received_level,
                'r_receiver_level' => @$firstReceiver->total_sender_level,
            ];
            $zigoData = $zigoData->merge($values);
        }

        //        dispatch(new SendGiftToZegoJob($zigoData, $totalPrice, ($request->to_zego == 1)))->onQueue('sendGiftToZigo');
        /* $startTime = microtime(true); */
        return $this->sendZigoGifts($zigoData, $totalPrice, ($isToZigo));
    }

    public function sendZigoGifts($zigoData, $totalPrice, $isToZego): array
    {
        //        Common::sendToZego_2('SendBroadcastMessage', $zigoData['room_id'], $zigoData['sender_id'], $zigoData['from_name'], "  {$zigoData['number']} x ارسل هدية  " . " قيمتها {$zigoData['gift_price']} " . " الى {$zigoData['to_name']}");
        if ($isToZego) {
            $d = [
                'messageContent' => [
                    'message' => 'showGifts',
                    'showGift' => $zigoData['show_gift'],
                    'giftImg' => $zigoData['gift_img'],
                    'gift_id' => $zigoData['gift_id'],
                    'send_id' => $zigoData['sender_id'],
                    'receiver_id' => $zigoData['receiver_id'],
                    'isExpensive' => $totalPrice >= 2000,
                    'num_gift' => $zigoData['number'],
                    'plural' => $zigoData['plural'],
                    'gift_price' => $totalPrice, // $zigoData['room_session'],,
                    'giftTP' => $totalPrice,
                    'coins' => @$zigoData['coins'] ?? '0',
                    'type' => @$zigoData['gift_image_type'] ?? 'mp4',

                ],
            ];
            $json = json_encode($d);
            $jsons[] = $json;
        }

        //            Common::sendToZego('SendCustomCommand', $zigoData['room_id'], $zigoData['sender_id'], $json);
        if ($totalPrice >= 2000) {
            $d = [
                'messageContent' => [
                    'msg' => 'SHB',
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
                    'oId' => (int) $zigoData['owner_id'],
                    'isPass' => $zigoData['is_password'],
                    'GTP' => $totalPrice,

                ],
            ];
            $json = json_encode($d);

            //                Common::sendToZego('SendCustomCommand', $zigoData['room_id'], $zigoData['sender_id'], $json);

            //                dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['sender_id'], $zigoData['room_id']), 'heavyProcessing');
        }

        return @$jsons ?? [];
    }

    public function updateRoomCoinsToUser($userId, $room, $totalPrice): void
    {
        $roomTopUsersRepository = $this->getRoomTopUsersRepository();
        if (! $roomTopUsersRepository) {
            return;
        }
        $topUser = $roomTopUsersRepository->findOrCreate($room->id, $userId);
        if ($topUser) {
            $topUser->coins += $totalPrice;
            $topUser->save();
        }
    }

    private function getRoomTopUsersRepository()
    {
        if (app()->bound(RoomTopUsersRepositoryContract::class)) {
            return app(RoomTopUsersRepositoryContract::class);
        }

        return (interface_exists(RoomTopUsersRepositoryContract::class) || class_exists(RoomTopUsersRepositoryContract::class)) ? app(RoomTopUsersRepositoryContract::class) : null;
    }

    private function getRoomRepository()
    {
        // Try bound contract first
        if (app()->bound(RoomRepositoryContract::class)) {
            return app(RoomRepositoryContract::class);
        }

        // Try if it exists
        if (interface_exists(RoomRepositoryContract::class) || class_exists(RoomRepositoryContract::class)) {
            try {
                return app(RoomRepositoryContract::class);
            } catch (Exception $e) {
                Log::warning('GiftLogService: Could not resolve RoomRepositoryContract even though it exists. Error: '.$e->getMessage());
            }
        }

        // Fallback to internal package interface if the contract is missing
        if (app()->bound(RoomRepoInterface::class)) {
            return app(RoomRepoInterface::class);
        }

        Log::error('GiftLogService: Room repository not found. Checked: '.RoomRepositoryContract::class.', '.RoomRepoInterface::class);

        return null;
    }

    private function getUserRepository()
    {
        // 1. Try resolving via agency package (most common)
        if (class_exists(AgencyUserRepository::class)) {
            return app(AgencyUserRepository::class);
        }

        // 2. Try contract if exists
        if (app()->bound(UserRepositoryContract::class)) {
            return app(UserRepositoryContract::class);
        }

        return null;
    }

    /**
     * @throws Throwable
     */
    private function checkGiftAvailability($user, $gift, $number, $type, $totalPrice)
    {
        if ($type === 'bag') {

            $existingGiftCount = UserGift::where('user_id', $user->id)
                ->where('gift_id', $gift->id)
                ->where(function ($query) {
                    $query->where('expire', 0)
                        ->orWhereRaw('DATE_ADD(created_at, INTERVAL expire DAY) >= NOW()');
                })->first();

            throw_if((! $existingGiftCount || $existingGiftCount->quantity < $number), Exception::class, 'Receiver has reached maximum allowed gifts');

            return null;
        }

        throw_if(
            $user->di < $totalPrice,
            Exception::class,
            'Insufficient balance, please go to recharge!'
        );

        return null;
    }

    private function gift_event($gift, $receivedUsers, $user, $totalPrice, $receivedUser, $receiversIds, $room, $ownerId, $number)
    {
        $gift_data = [
            'show_gift' => $gift->show_img ?: $gift->show_img2,
            'gift_img' => $gift->img,
            'gift_id' => $gift->id,
            'sender_id' => (int) $user->id,
            'receiver_id' => @(int) $receivedUser->id,
            'num_gift' => $totalPrice,
            'plural' => is_array($receiversIds) && count($receiversIds) > 1,
            'room_session' => $room->session_string,
            'is_password' => (bool) (@$room->room_pass),
            'room_uuid' => $room->owner?->uuid ?: 0,
            'room_id' => (string) ($room->id ?: 0),
            'room_owner_id' => $room->uid ?: 0,
            'room_name' => $room->room_name ?: '',
            'room_mode' => $room->mode,
            'room_cover' => $room->room_cover ?? '',
            'room_background' => $room->final_room_image ?? '',
            'from_name' => $user->name,
            'to_name' => @$receivedUser->name,
            'gift_price' => $gift->price,
            'owner_id' => $ownerId,
            'number' => $number,
            'coins' => $user->coins_string,
            'gift_image_type' => $gift->image_type,
            's_vip_level' => @$user->userVip->level ?? 0,
            's_image' => @$user->profile->avatar ?? '',
            's_name' => @$user->name ?? '',
            's_sender_level' => @$user->total_sender_level,
            's_receiver_level' => @$user->total_received_level,
            'r_vip_level' => @$receivedUser->userVip->level ?? 0,
            'r_name' => @$receivedUser->name ?? '',
            'r_image' => @$receivedUser->profile->avatar ?? '',
            'r_sender_level' => @$receivedUser->total_received_level,
            'r_receiver_level' => @$receivedUser->total_sender_level,
            'room_type' => @$room->type,
        ];

        $this->fireGiftBannerEvent($gift_data);
    }

    /**
     * Helper methods لإدارة Classes الخارجية بشكل آمن
     */
    private function getGiftSourceTypeClass()
    {
        return GiftSourceType::class;
    }

    private function getUserCoinLogTypeClass()
    {
        return UserCoinLogType::class;
    }

    private function getCacheHelperClass()
    {
        return CacheHelper::class;
    }

    private function getUserCoinLogHelperClass()
    {
        return UserCoinLogHelper::class;
    }

    private function getCommonHelperClass()
    {
        return Common::class;
    }

    private function getUpdatePkJobClass()
    {
        return PackageHelper::isInstalled('pk') ? UpdatePkAndSendToZigoJob::class : null;
    }

    private function getSendGiftServiceInstance()
    {
        return new SendGiftService();
    }

    private function getCpModelClass()
    {
        return PackageHelper::isInstalled('cp') ? Cp::class : null;
    }

    private function getReceiverGiftDTOClass()
    {
        return ReceiverGiftDTO::class;
    }

    private function fireGiftBannerEvent($data)
    {
        event(new GiftBannerEvent($data));
    }

    /**
     * Helper methods لإدارة Modules الخارجية بشكل آمن
     */
    private function hasCpService(): bool
    {
        return PackageHelper::isInstalled('cp');
    }

    private function getCpService()
    {
        if ($this->hasCpService()) {
            return new CpService();
        }

        return null;
    }

    private function hasCharizmaJob(): bool
    {
        return PackageHelper::isInstalled('charisma');
    }

    private function getCharizmaJob($roomId, $userIds, $amount, $senderId)
    {
        if ($this->hasCharizmaJob()) {
            return new UpdateSendCharismaToZigo($roomId, $userIds, $amount, $senderId);
        }

        return null;
    }

    private function hasRoomBoomService(): bool
    {
        return PackageHelper::isInstalled('roomBoom');
    }

    private function getRoomBoomService()
    {
        if ($this->hasRoomBoomService()) {
            return new NewRoomBoomGiftService();
        }

        return null;
    }
}
