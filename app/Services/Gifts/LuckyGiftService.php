<?php

namespace App\Services\Gifts;

use App\Http\Services\RoomService;
use App\Models\Cp;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Models\CoreWallet;
use App\Facades\RedisService;
use InvalidArgumentException;
use App\Enums\UserCoinLogType;
use App\Jobs\LogUserCoinProfit;
use App\Helpers\UserCoinLogHelper;
use App\Traits\Gifts\WinLuckyGift;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Classes\Gifts\SendGiftService;
use Modules\CP\Http\Services\CpService;
use App\Exceptions\NotInfMoneyException;
use App\Jobs\LogUserCumulativeCoinProfit;
use App\Traits\Gifts\LuckyGiftProbability;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use Illuminate\Validation\ValidationException;
use Modules\Public\Http\Services\UpgradeRoomLevelServices;
use Carbon\Carbon;
use App\Helpers\CacheHelper;
use Illuminate\Support\Facades\Cache;
use Modules\RoomBoom\Services\NewRoomBoomGiftService;
use Modules\Charizma\Jobs\UpdateSendCharismaToZigo;

class LuckyGiftService
{

    use LuckyGiftProbability;
    use WinLuckyGift;

    private UpdateUserWhenSendGift $updateUserWhenSendGift;

    public function send($data)
    {
    }

    private function acquireUserLock(int $userId, int $timeoutSeconds = 30): \Illuminate\Contracts\Cache\Lock
    {
     /*   $lock = Cache::lock(
            "lucky_gift_lock:user:{$userId}",
            $timeoutSeconds
        );

        if (!$lock->get()) {
            throw new InvalidArgumentException(__('api_responses.try_again'));
        }

        return $lock;*/
        $lock = Cache::lock("lucky_gift_lock:user:{$userId}", $timeoutSeconds);

        try {
            $lock->block(5);
        } catch (LockTimeoutException $e) {
            throw new InvalidArgumentException(__('api_responses.try_again'));
        }

        return $lock;
    }

    public function sendLuckyGift2(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId = $user->id;
        $ownerId = @$data['owner_id'];
        $roomId = @$data['room_id'];
        $giftId = $data['id'];
        $number = $data['num'];
        $count = $data['count'] ?? 1;
        $amountBefore = $user->di;
        $appPercentage = getGiftPercentage('app_wallet_lucky_gift') / 10;
        $roomrPercentage = getGiftPercentage('owner_lucky_gift') / 10;
        $hostPercentage = getGiftPercentage('host_lucky_gift') / 10;
        $total_cashback_percentage = 0;


        $gift = Gift::query()->select(['id', 'name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));

        $giftPrice = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);
        $totalPrice = $giftPrice * $numberOfGift;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPrice) {
            throw new InvalidArgumentException(__('api_responses.insufficient'));
        }

        $lock = $this->acquireUserLock($userId);
        try {
            $user->refresh();
            $userCoins = $user->di;
            $oldUserCoin = $userCoins;
            $amountBefore = $user->di;

            if ($userCoins < $totalPrice) {
                throw new InvalidArgumentException(__('api_responses.insufficient'));
            }

        if (isset($ownerId)) {
            $room = Room::withoutAppends()
                ->where('uid', $ownerId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
        } else {
            $room = Room::withoutAppends()
                ->where('id', $roomId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
            $ownerId = $room?->uid;
        }

        if (!$room)
            throw new InvalidArgumentException(__('api_responses.roomNotFound'));

        $roomId = $room->id;



        /// todo check visitors

        [$ownerWallet, $appWallet] = $this->getCoreWallets();
        if (!($appWallet instanceof CoreWallet) || !($ownerWallet instanceof CoreWallet))
            throw new InvalidArgumentException('app dosn\'t resolved ');
        $firstAppWalletCoins = $appWallet->coins;
        $firstOwnerWalletCoins = $ownerWallet->coins;


        $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name', 'agency_id'])->get();
        $receiverName = $receivedUsers->first()?->name;
        $receiversCount = $receivedUsers->count();
        $isToRoom = $receiversCount > 1;



        //        $responseData = $this->getResponseData($gift, $room->microphone, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));
        $responseData = $this->getResponseData2($gift, $room, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

        $index = $count;

        $coinsForReceiver = $number * ($giftPrice * $hostPercentage);
        $price = $coinsForReceiver * $receiversCount;
        $total_user_win = 0;
        $total_count_win = 0;

        UserCoinLogHelper::logByType(
            $user->id,
            -abs($totalPrice),
            $amountBefore,
            UserCoinLogType::LUCKY_GIFT,
            $gift?->name,
        );

        $totalPrice = $giftPrice * $number * $receiversCount;
        $coinsForApp = $totalPrice * $appPercentage;
        $coinsForOwner = $totalPrice * $roomrPercentage;

        while ($user->di >= $totalPrice && $index > 0) {

            // $appWallet->coins   += $price * 8;
            $appWallet->coins += $coinsForApp;
            $ownerWallet->coins += $price; //        $appWallet->save();
            //        $ownerWallet->save();
            $isWinner = $this->is_winner($gift);

            $isPopular = false;
            $totalGiftPrice = $giftPrice * $number;
            $appWalletCoins = $appWallet->coins;
            if ($isWinner && $appWalletCoins > ($totalGiftPrice)) {
                $properties = $gift->luckyGift?->min_percentage;

                $cashback_percentage = $this->getTimesOfPrice($appWalletCoins, $totalGiftPrice, $properties);

                $cashback_value = $cashback_percentage * $giftPrice * $number;
                if ($cashback_percentage > 0) {

                    $user->enableSaving = false;
                    $user->di += $cashback_value;
                    //                $user->save();
                    $appWallet->coins -= $cashback_value;
                    //                $appWallet->save();
                    if ($cashback_percentage > 1) {
                        $message = $this->winnerMessage($cashback_percentage);
                    }
                } else {
                    $isWinner = false;
                }

                //send to zigo this data to show in all rooms if cashback percentage > 20
                $isPopular = $this->isPopular($cashback_percentage);

                if ($isPopular) {



                    $this->sendPopularToZego($userId, $user, $gift, $ownerId, $room, $cashback_percentage, cashbackValue: $cashback_value);
                }
            } else {
                $cashback_percentage = 0;
            }

            [$commentMessage, $sendMessage] =
                $this->getSendMessage($giftPrice, $message ?? null, $receiverName, $number, isToRoom: $isToRoom);

            $responseData['combo'][] = [
                'status' => 0,
                'data' => [
                    'win_coins' => (int) ($totalGiftPrice * $cashback_percentage),
                    'is_win' => $isWinner,
                    'is_popular' => $isPopular,
                    'comment_message' => $commentMessage,
                    'winner_comment' => $sendMessage,
                ],
                'error_message' => '',
            ];

            $user->di -= $totalPrice;
            $index--;
            $message = null;
            $total_user_win += ($totalGiftPrice * $cashback_percentage);
            $total_count_win += 1;
            $total_count_win += 1;
            $total_cashback_percentage += $cashback_percentage;
            $cashback_percentage = 0;
            //            $this->save_data_win_for_user($user->id,$totalGiftPrice,$cashback_percentage);
        }



        if ($total_user_win > 0) {

            UserCoinLogHelper::logByType(
                $userId,
                $total_user_win,
                ($user->di - $total_user_win),
                UserCoinLogType::CASHBACK,
                null,
            );
        }


        if ($index > 0) {
            $count -= $index;
            $responseData['combo'][] = [
                'status' => 1,
                'data' => null,
                'error_message' => __('api_responses.insufficient'),
            ];
        }

        // update room session
        // $room->session      += (int)$gift->price * $number * $count * 0.1;
        $room->session += $coinsForOwner;
        $room->save();


        // add session to response
        $responseData['session'] = $room->session_string;

        //new user coins
        $responseData['user_coins'] = $userCoins;
        $responseData['gift_num'] = $receiversCount * $number * $count;
        $responseData['total_price'] = $totalPrice;
        // $responseData['cashback_percentage'] = $total_cashback_percentage;
        // $responseData['total_user_win'] = $total_user_win;

        //update user coins and diamond and sender level
        $totalDiamond = $totalPrice * $count;
        $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_sender_diamonds, $totalDiamond, $user->sub_sender_level);
        $this->updateUserCoins($user->id, $user->di, $userCoins, $totalDiamond, senderLevel: $senderLevel);


        // update core wallet
        $diffAppWallet = $appWallet->coins - $firstAppWalletCoins;
        $diffOwnerWallet = $ownerWallet->coins - $firstOwnerWalletCoins;
        $this->updateCoreWallet($diffAppWallet, $diffOwnerWallet);


        $coinsForReceiver = $coinsForReceiver * $count;
        $number = $number * $count;

        $newUserCoin = ($user->di - $userCoins);
        $this->updateCache($userId, $roomId, $receiversIds, $giftId, $data, $number, $price, $coinsForReceiver, $oldUserCoin, $newUserCoin, $total_user_win, $total_count_win);

        if ($room->charizma_status && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
        } elseif ($room->lastPk && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
        }

        $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);

        // \Log::info('sendLuckyGift2 - Room Type Check', [
        //     'room_id' => $room->id,
        //     'room_type' => $room->type,
        //     'total_diamond' => $room->total_diamond,
        //     'totalPrice' => $totalPrice,
        // ]);
        $responseData['total_pk'] = $coinsForReceiver;

        if ($room->type == 'audio') {
            $serviceLevel = new UpgradeRoomLevelServices();
            $serviceLevel->sendGift($room, $totalPrice);
        }

        return $responseData;
        } finally {
            $lock->release();
        }
    }

    /**
     * New variant that uses FairLuckService3 for each throw to calculate winnings
     */
    public function sendLuckyGift4(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId = $user->id;
        $ownerId = @$data['owner_id'];
        $roomId = @$data['room_id'];
        $giftId = $data['id'];
        $number = $data['num'];
        $count = $data['count'] ?? 1;
        $amountBefore = $user->di;

        $appFeeRate = \App\Models\FairLuckSetting::getAppFeeRate();
        $receiverFeeRate = \App\Models\FairLuckSetting::getReceiverFeeRate();
        $roomrPercentage = \App\Models\FairLuckSetting::getOwnerFeeRate();
        $hostPercentage = $receiverFeeRate;
        $total_cashback_percentage = 0;


        $gift = Gift::query()->select(['id', 'name', 'e_name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));



        $giftPrice = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $receiversCount = count($receiversIds);
        $numberOfGift = $number * $receiversCount;
        $totalPrice = $giftPrice * $numberOfGift;
        $totalPriceFull = $totalPrice * $count;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPriceFull) {
            throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
        }

        $lock = $this->acquireUserLock($userId);
        try {
            $user->refresh();
            $userCoins = $user->di;
            $oldUserCoin = $userCoins;
            $amountBefore = $user->di;

            if ($userCoins < $totalPriceFull) {
                throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
            }

        if (isset($ownerId)) {
            $room = Room::withoutAppends()
                ->where('uid', $ownerId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
        } else {
            $room = Room::withoutAppends()
                ->where('id', $roomId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
            $ownerId = $room?->uid;
        }

        if (!$room)
            throw new InvalidArgumentException(__('api_responses.roomNotFound'));

        $roomId = $room->id;


        $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name', 'agency_id'])->get();
        $receiverName = $receivedUsers->first()?->name;
        $receiversCount = $receivedUsers->count();
        $isToRoom = $receiversCount > 1;
        $responseData = $this->getResponseData2($gift, $room, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

        $index = $count;
        $total_user_win = 0;
        $max_single_win = 0;
        $total_count_win = 0;
        $unitPrice = $giftPrice * $number;
        $fairService = app(\App\Services\FairLuck\FairLuckService3::class);
        $throwNumber = 0;

        $coinsForOwner = ($giftPrice * $number * $receiversCount) * $roomrPercentage;



        $senderBalanceBefore = $user->di;
        $totalWalletsBefore = null;
        $totalWalletsAfter = null;

        $totalPriceFull = $giftPrice * $number * $receiversCount;

        while ($user->di >= $totalPriceFull && $index > 0) {
            $balanceBeforeIteration = $user->di;
            UserCoinLogHelper::logByType(
                $user->id,
                -abs($totalPriceFull),
                $balanceBeforeIteration,
                UserCoinLogType::LUCKY_GIFT,
                $gift?->name,
            );

            foreach ($receiversIds as $receiverId) {

                if ($user->di < $unitPrice) {
                    $index = 0;
                    break;
                }

                $throwNumber++;

                $senderBalanceBeforeHit = $user->di;

                $appFee = $unitPrice * $appFeeRate;
                $receiverFee = $unitPrice * $receiverFeeRate;
                $netBetAmount = $unitPrice - $appFee - $receiverFee - $coinsForOwner;

                try {
                    $result = $fairService->processBet($user, $gift, $netBetAmount, $roomId, $receiverId, $appFee, $receiverFee, $senderBalanceBeforeHit, $user->di - $unitPrice);
                } catch (\Throwable $e) {
                    Log::error('FairLuckService3 processBet FAILED', [
                        'user_id' => $userId,
                        'throw_number' => $throwNumber,
                        'receiver_id' => $receiverId,
                        'error' => $e->getMessage(),
                    ]);
                    $result = null;
                }

                $iterationWin = 0;
                $isWinner = false;
                $multiplier = 0;
                $message = null;

                if ($result) {
                    $isWinner = (bool) ($result->isWinner ?? false);
                    $multiplier = (float) ($result->multiplier ?? 0);
                    $iterationWin = (float) ($result->profitAmount ?? 0);

                    if ($isWinner && $iterationWin > 0) {
                        $user->enableSaving = false;
                        $user->di += $iterationWin;

                        $total_user_win += $iterationWin;
                        $total_count_win++;

                        if ($multiplier > 1) {
                            $message = $this->winnerMessage($multiplier);
                        }
                    }

                    if ($totalWalletsBefore === null) {
                        $totalWalletsBefore = $result->wallets_before ?? null;
                    }
                    $totalWalletsAfter = $result->wallets_after ?? null;
                }

                $isPopular = $multiplier >= 5;

                if ($isPopular && $iterationWin > 0) {
                    $this->sendPopularToZegoV2(
                        $userId,
                        $user,
                        $gift,
                        $ownerId,
                        $room,
                        $multiplier,
                        cashbackValue: $iterationWin
                    );
                }

                [$commentMessage, $sendMessage] = $this->getSendMessage(
                    $giftPrice,
                    $message,
                    $receiverName,
                    $number,
                    isToRoom: $isToRoom
                );

                // After this hit: deduct unitPrice, add iterationWin if winner
                $senderBalanceAfterHit = (int) ($senderBalanceBeforeHit - $unitPrice + ($iterationWin > 0 ? $iterationWin : 0));

                $responseData['combo'][] = [
                    'status' => 0,
                    'data' => [
                        'win_coins' => (int) $iterationWin,
                        'is_win' => $isWinner,
                        'is_popular' => $isPopular,
                        'comment_message' => $commentMessage,
                        'winner_comment' => $sendMessage,
                    ],
                    'error_message' => '',
                    'sender_balance_before' => (int) $senderBalanceBeforeHit,
                    'sender_balance_after' => $senderBalanceAfterHit,
                    'wallets_before' => $result->wallets_before ?? null,
                    'wallets_after' => $result->wallets_after ?? null,
                ];

                $user->di -= $unitPrice;
                $total_cashback_percentage += $multiplier;
            }

            $index--;
        }



        if ($total_user_win > 0) {
            UserCoinLogHelper::logByType(
                $userId,
                $total_user_win,
                ($user->di - $total_user_win),
                UserCoinLogType::CASHBACK,
                null,
            );

        }

        if ($index > 0) {
            $count -= $index;

            $responseData['combo'][] = [
                'status' => 1,
                'data' => null,
                'error_message' => __('api_responses.insufficient'),
            ];
        }

        $room->session += $coinsForOwner * $count;
        $room->save();

        $responseData['session'] = $room->session_string;
        $responseData['user_coins'] = $user->di;
        $responseData['gift_num'] = $receiversCount * $number * $count;
        $responseData['total_price'] = $totalPrice;
        $responseData['cashback_percentage'] = $total_cashback_percentage;
        $responseData['total_user_win'] = $total_user_win;
        $responseData['gift_name'] = app()->getLocale() === 'ar' ? $gift->name ?? $gift->e_name : $gift->e_name ?? $gift->name;
        $responseData['summary'] = [
            'total_win' => $total_user_win,
            'max_single_win' => $max_single_win,
            'total_win_count' => $total_count_win,
        ];

        $responseData['balances'] = [
            'sender' => [
                'before' => (int) $senderBalanceBefore,
                'after' => (int) $user->di,
            ],
            'wallets' => [
                'before' => $totalWalletsBefore,
                'after' => $totalWalletsAfter,
            ],
        ];

        // Update user coins and diamond
        $totalDiamond = $totalPrice * $count;
        $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_diamond_send, $totalDiamond, $user->sub_sender_level);
        $this->updateUserCoins($user->id, $user->di, $userCoins, $totalDiamond, senderLevel: $senderLevel);



        $coinsForReceiverBase = $number * ($giftPrice * $hostPercentage);
        $price = $coinsForReceiverBase * $receiversCount;
        $coinsForReceiver = $coinsForReceiverBase * $count;
        $number = $number * $count;

        $newUserCoin = ($user->di - $userCoins);
        $this->updateCache($userId, $roomId, $receiversIds, $giftId, $data, $number, $price, $coinsForReceiver, $oldUserCoin, $newUserCoin, $total_user_win, $total_count_win);

        if ($room->charizma_status && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
        } elseif ($room->lastPk && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
        }

        $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);

        // Update total_room_gifts table
        $settings = \App\Helpers\CacheHelper::cacheSettings();
        if (gettype($settings) !== 'array') {
            $settings = $settings->pluck('value', 'key')->toArray();
        }
        $roomBoomSettings = $settings['room_boom'] ?? 1;
        $totalHostDiamond = (int) $totalPrice * $hostPercentage;
        if ($roomBoomSettings) {
            (new NewRoomBoomGiftService())->sendGift($room, $totalHostDiamond, $userId);
        } else {
            $tz = getTimezone();
            $todayStart = \Carbon\Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');
            $totalRoomGift = (new RoomService())->getOrCreateTotalRoomGift($room->id, $todayStart);
            $totalRoomGift->increment('current_total', $totalHostDiamond);
        }
        $responseData['total_pk'] = $coinsForReceiver;

        if ($room->type == 'audio') {
            $serviceLevel = new UpgradeRoomLevelServices();
            $serviceLevel->sendGift($room, $totalPrice * $count);
        }

        return $responseData;
        } finally {
            $lock->release();
        }
    }

    /**
     * Variant V5 that uses FairLuckServiceV5 for each throw to calculate winnings
     */
    public function sendLuckyGiftV5(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId = $user->id;
        $ownerId = @$data['owner_id'];
        $roomId = @$data['room_id'];
        $giftId = $data['id'];
        $number = $data['num'];
        $count = $data['count'] ?? 1;
        $amountBefore = $user->di;
        $appFeeRate = \App\Models\FairLuckSetting::getAppFeeRate();
        $receiverFeeRate = \App\Models\FairLuckSetting::getReceiverFeeRate();
        $roomPercentage = \App\Models\FairLuckSetting::getOwnerFeeRate();
        $hostPercentage = $receiverFeeRate;
        $total_cashback_percentage = 0;


        $gift = Gift::query()->select(['id', 'name', 'e_name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));


        $giftPrice = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $receiversCount = count($receiversIds);
        $numberOfGift = $number * $receiversCount;
        $totalPrice = $giftPrice * $numberOfGift;
        $totalPriceFull = $totalPrice * $count;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPriceFull) {
            throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
        }

        $lock = $this->acquireUserLock($userId);
        try {
            $user->refresh();
            $userCoins = $user->di;
            $oldUserCoin = $userCoins;
            $amountBefore = $user->di;

            if ($userCoins < $totalPriceFull) {
                throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
            }

        if (isset($ownerId)) {
            $room = Room::withoutAppends()
                ->where('uid', $ownerId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
        } else {
            $room = Room::withoutAppends()
                ->where('id', $roomId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
            $ownerId = $room?->uid;
        }

        if (!$room)
            throw new InvalidArgumentException(__('api_responses.roomNotFound'));

        $roomId = $room->id;


        $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name', 'agency_id'])->get();
        $receiverName = $receivedUsers->first()?->name;
        $receiversCount = $receivedUsers->count();
        $isToRoom = $receiversCount > 1;
        $responseData = $this->getResponseData2($gift, $room, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

        $index = $count;
        $total_user_win = 0;
        $max_single_win = 0;
        $total_count_win = 0;
        $unitPrice = $giftPrice * $number;
        $fairService = app(\App\Services\FairLuck\V5\FairLuckServiceV5::class);
        $throwNumber = 0;

        $coinsForOwner = 0; // V5 logic handles distribution inside processBet?



        $senderBalanceBefore = $user->di;
        $totalWalletsBefore = null;
        $totalWalletsAfter = null;

        $totalPriceFull = $giftPrice * $number * $receiversCount;

        while ($user->di >= $totalPriceFull && $index > 0) {
            $balanceBeforeIteration = $user->di;
            UserCoinLogHelper::logByType(
                $user->id,
                -abs($totalPriceFull),
                $balanceBeforeIteration,
                UserCoinLogType::LUCKY_GIFT,
                $gift?->name,
            );

            foreach ($receiversIds as $receiverId) {

                if ($user->di < $unitPrice) {
                    $index = 0;
                    break;
                }

                $throwNumber++;

                $senderBalanceBeforeHit = $user->di;

                $appFee = $unitPrice * $appFeeRate;
                $receiverFee = $unitPrice * $receiverFeeRate;

                // V5 logic handles its own distribution, netBetAmount passed to processBet
                $netBetAmount = $unitPrice - $appFee - $receiverFee;

                try {
                    $result = $fairService->processBet(
                        $user,
                        $gift,
                        $netBetAmount,
                        $roomId,
                        $receiverId,
                        $appFee,
                        $receiverFee,
                        $senderBalanceBeforeHit,
                        $user->di - $unitPrice
                    );
                } catch (\Throwable $e) {
                    Log::error('FairLuckServiceV5 processBet FAILED', [
                        'user_id' => $userId,
                        'throw_number' => $throwNumber,
                        'receiver_id' => $receiverId,
                        'error' => $e->getMessage(),
                    ]);
                    $result = null;
                }

                $iterationWin = 0;
                $isWinner = false;
                $multiplier = 0;
                $message = null;

                if ($result) {
                    $isWinner = (bool) ($result->isWinner ?? false);
                    $multiplier = (float) ($result->multiplier ?? 0);
                    $iterationWin = (float) ($result->profitAmount ?? 0);

                    if ($isWinner && $iterationWin > 0) {
                        $user->enableSaving = false;
                        $user->di += $iterationWin;

                        $total_user_win += $iterationWin;
                        $total_count_win++;

                        if ($multiplier > 1) {
                            $message = $this->winnerMessage($multiplier);
                        }
                    }

                    if ($totalWalletsBefore === null) {
                        $totalWalletsBefore = $result->wallets_before ?? null;
                    }
                    $totalWalletsAfter = $result->wallets_after ?? null;
                }

                $isPopular = $multiplier >= 250; // V5 jackpot threshold

                if ($isPopular && $iterationWin > 0) {
                    $this->sendPopularToZegoV2(
                        $userId,
                        $user,
                        $gift,
                        $ownerId,
                        $room,
                        $multiplier,
                        cashbackValue: $iterationWin
                    );
                }

                [$commentMessage, $sendMessage] = $this->getSendMessage(
                    $giftPrice,
                    $message,
                    $receiverName,
                    $number,
                    isToRoom: $isToRoom
                );

                // After this hit: deduct unitPrice, add iterationWin if winner
                $senderBalanceAfterHit = (int) ($senderBalanceBeforeHit - $unitPrice + ($iterationWin > 0 ? $iterationWin : 0));

                $responseData['combo'][] = [
                    'status' => 0,
                    'data' => [
                        'win_coins' => (int) $iterationWin,
                        'is_win' => $isWinner,
                        'is_popular' => $isPopular,
                        'comment_message' => $commentMessage,
                        'winner_comment' => $sendMessage,
                    ],
                    'error_message' => '',
                    'sender_balance_before' => (int) $senderBalanceBeforeHit,
                    'sender_balance_after' => $senderBalanceAfterHit,
                    'wallets_before' => $result->wallets_before ?? null,
                    'wallets_after' => $result->wallets_after ?? null,
                ];

                $user->di -= $unitPrice;
                $total_cashback_percentage += $multiplier;
            }

            $index--;
        }



        if ($total_user_win > 0) {
            UserCoinLogHelper::logByType(
                $userId,
                $total_user_win,
                ($user->di - $total_user_win),
                UserCoinLogType::CASHBACK,
                null,
            );

        }

        if ($index > 0) {
            $count -= $index;

            $responseData['combo'][] = [
                'status' => 1,
                'data' => null,
                'error_message' => __('api_responses.insufficient'),
            ];
        }

        // Room session update (fixed for V5 context if needed)
        $room->session += (int)($totalPrice * $count * 0.05);
        $room->save();

        $responseData['session'] = $room->session_string;
        $responseData['user_coins'] = $user->di;
        $responseData['gift_num'] = $receiversCount * $number * $count;
        $responseData['total_price'] = $totalPrice;
        $responseData['cashback_percentage'] = $total_cashback_percentage;
        $responseData['total_user_win'] = $total_user_win;
        $responseData['gift_name'] = app()->getLocale() === 'ar' ? $gift->name ?? $gift->e_name : $gift->e_name ?? $gift->name;
        $responseData['summary'] = [
            'total_win' => $total_user_win,
            'max_single_win' => $max_single_win,
            'total_win_count' => $total_count_win,
        ];

        $responseData['balances'] = [
            'sender' => [
                'before' => (int) $senderBalanceBefore,
                'after' => (int) $user->di,
            ],
            'wallets' => [
                'before' => $totalWalletsBefore,
                'after' => $totalWalletsAfter,
            ],
        ];

        // Update user coins and diamond
        $totalDiamond = $totalPrice * $count;
        $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_diamond_send, $totalDiamond, $user->sub_sender_level);
        $this->updateUserCoins($user->id, $user->di, $userCoins, $totalDiamond, senderLevel: $senderLevel);


        $coinsForReceiverBase = $number * ($giftPrice * $hostPercentage);
        $price = $coinsForReceiverBase * $receiversCount;
        $coinsForReceiver = $coinsForReceiverBase * $count;
        $number = $number * $count;

        $newUserCoin = ($user->di - $userCoins);
        $this->updateCache($userId, $roomId, $receiversIds, $giftId, $data, $number, $price, $coinsForReceiver, $oldUserCoin, $newUserCoin, $total_user_win, $total_count_win);

        if ($room->charizma_status && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
        } elseif ($room->lastPk && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
        }

        $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);

        $responseData['total_pk'] = $coinsForReceiver;

        if ($room->type == 'audio') {
            $serviceLevel = new UpgradeRoomLevelServices();
            $serviceLevel->sendGift($room, $totalPrice * $count);
        }

        return $responseData;
        } finally {
            $lock->release();
        }
    }



    public function sendLuckyGift6(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId = $user->id;
        $ownerId = @$data['owner_id'];
        $roomId = @$data['room_id'];
        $giftId = $data['id'];
        $number = $data['num'];
        $count = $data['count'] ?? 1;
        $amountBefore = $user->di;

        $appFeeRate = \App\Models\FairLuckSetting::getAppFeeRate();
        $receiverFeeRate = \App\Models\FairLuckSetting::getReceiverFeeRate();
        $roomPercentage = \App\Models\FairLuckSetting::getOwnerFeeRate();
        $hostPercentage = $receiverFeeRate;
        $total_cashback_percentage = 0;

        $gift = Gift::query()->select(['id', 'name', 'e_name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));

        $giftPrice = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $receiversCount = count($receiversIds);
        $numberOfGift = $number * $receiversCount;
        $totalPrice = $giftPrice * $numberOfGift;
        $totalPriceFull = $totalPrice * $count;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPriceFull) {
            throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
        }

        $lock = $this->acquireUserLock($userId);
        try {
            $user->refresh();
            $userCoins = $user->di;
            $oldUserCoin = $userCoins;
            $amountBefore = $user->di;

            if ($userCoins < $totalPriceFull) {
                throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
            }

            if (isset($ownerId)) {
                $room = Room::withoutAppends()
                    ->where('uid', $ownerId)
                    ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                    ->first();
            } else {
                $room = Room::withoutAppends()
                    ->where('id', $roomId)
                    ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                    ->first();
                $ownerId = $room?->uid;
            }

            if (!$room)
                throw new InvalidArgumentException(__('api_responses.roomNotFound'));

            $roomId = $room->id;

            $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name', 'agency_id'])->get();
            $receiverName = $receivedUsers->first()?->name;
            $receiversCount = $receivedUsers->count();
            $isToRoom = $receiversCount > 1;
            $responseData = $this->getResponseData2($gift, $room, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

            $index = $count;
            $total_user_win = 0;
            $max_single_win = 0;
            $total_count_win = 0;
            $unitPrice = $giftPrice * $number;
            $fairService = app(\App\Services\FairLuck\V7\FairLuckServiceV7::class);
            $throwNumber = 0;

            // Fixed: per-receiver room owner fee (was using total for all receivers before)
            $coinsForOwnerPerReceiver = $unitPrice * $roomPercentage;
            $coinsForOwnerTotal = $coinsForOwnerPerReceiver * $receiversCount;

            $senderBalanceBefore = $user->di;
            $totalWalletsBefore = null;
            $totalWalletsAfter = null;

            $totalPriceFull = $giftPrice * $number * $receiversCount;

            while ($user->di >= $totalPriceFull && $index > 0) {
                $balanceBeforeIteration = $user->di;
                UserCoinLogHelper::logByType(
                    $user->id,
                    -abs($totalPriceFull),
                    $balanceBeforeIteration,
                    UserCoinLogType::LUCKY_GIFT,
                    $gift?->name,
                );

                foreach ($receiversIds as $receiverId) {

                    if ($user->di < $unitPrice) {
                        $index = 0;
                        break;
                    }

                    $throwNumber++;

                    $senderBalanceBeforeHit = $user->di;

                    // Fixed: use per-receiver room fee instead of total
                    $appFee = $unitPrice * $appFeeRate;
                    $receiverFee = $unitPrice * $receiverFeeRate;
                    $netBetAmount = $unitPrice - $appFee - $receiverFee - $coinsForOwnerPerReceiver;

                    try {
                        $result = $fairService->processBet(
                            $user,
                            $gift,
                            $netBetAmount,
                            $unitPrice,
                            $roomId,
                            $receiverId,
                            $appFee,
                            $receiverFee,
                            $senderBalanceBeforeHit,
                            $user->di - $unitPrice
                        );
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('FairLuckServiceV7 processBet FAILED', [
                            'user_id' => $userId,
                            'throw_number' => $throwNumber,
                            'receiver_id' => $receiverId,
                            'error' => $e->getMessage(),
                        ]);
                        $result = null;
                    }

                    $iterationWin = 0;
                    $isWinner = false;
                    $multiplier = 0;
                    $message = null;

                    if ($result) {
                        $isWinner = (bool) ($result->isWinner ?? false);
                        $multiplier = (float) ($result->multiplier ?? 0);
                        $iterationWin = (float) ($result->profitAmount ?? 0);

                        if ($isWinner && $iterationWin > 0) {
                            $user->enableSaving = false;
                            $user->di += $iterationWin;

                            $total_user_win += $iterationWin;
                            $total_count_win++;
                            if ($iterationWin > $max_single_win) {
                                $max_single_win = $iterationWin;
                            }

                            if ($multiplier > 1) {
                                $message = $this->winnerMessage($multiplier);
                            }
                        }

                        if ($totalWalletsBefore === null) {
                            $totalWalletsBefore = $result->wallets_before ?? null;
                        }
                        $totalWalletsAfter = $result->wallets_after ?? null;
                    }

                    $isPopular = $multiplier >= 5;

                    if ($isPopular && $iterationWin > 0) {
                        $this->sendPopularToZegoV2(
                            $userId,
                            $user,
                            $gift,
                            $ownerId,
                            $room,
                            $multiplier,
                            cashbackValue: $iterationWin
                        );
                    }

                    [$commentMessage, $sendMessage] = $this->getSendMessage(
                        $giftPrice,
                        $message,
                        $receiverName,
                        $number,
                        isToRoom: $isToRoom
                    );

                    $senderBalanceAfterHit = (int) ($senderBalanceBeforeHit - $unitPrice + ($iterationWin > 0 ? $iterationWin : 0));

                    $responseData['combo'][] = [
                        'status' => 0,
                        'data' => [
                            'win_coins' => (int) $iterationWin,
                            'is_win' => $isWinner,
                            'is_popular' => $isPopular,
                            'comment_message' => $commentMessage,
                            'winner_comment' => $sendMessage,
                        ],
                        'error_message' => '',
                        'sender_balance_before' => (int) $senderBalanceBeforeHit,
                        'sender_balance_after' => $senderBalanceAfterHit,
                        'wallets_before' => $result?->wallets_before,
                        'wallets_after' => $result?->wallets_after,
                    ];

                    $user->di -= $unitPrice;
                    $total_cashback_percentage += $multiplier;
                }

                $index--;
            }

            if ($total_user_win > 0) {
                UserCoinLogHelper::logByType(
                    $userId,
                    $total_user_win,
                    ($user->di - $total_user_win),
                    UserCoinLogType::CASHBACK,
                    null,
                );
            }

            if ($index > 0) {
                $count -= $index;

                $responseData['combo'][] = [
                    'status' => 1,
                    'data' => null,
                    'error_message' => __('api_responses.insufficient'),
                ];
            }

            $room->session += $coinsForOwnerTotal * $count;
            $room->save();

            $responseData['session'] = $room->session_string;
            $responseData['user_coins'] = $user->di;
            $responseData['gift_num'] = $receiversCount * $number * $count;
            $responseData['total_price'] = $totalPrice;
            $responseData['cashback_percentage'] = $total_cashback_percentage;
            $responseData['total_user_win'] = $total_user_win;
            $responseData['gift_name'] = app()->getLocale() === 'ar' ? $gift->name ?? $gift->e_name : $gift->e_name ?? $gift->name;
            $responseData['summary'] = [
                'total_win' => $total_user_win,
                'max_single_win' => $max_single_win,
                'total_win_count' => $total_count_win,
            ];

            $responseData['balances'] = [
                'sender' => [
                    'before' => (int) $senderBalanceBefore,
                    'after' => (int) $user->di,
                ],
                'wallets' => [
                    'before' => $totalWalletsBefore,
                    'after' => $totalWalletsAfter,
                ],
            ];

            // Update user coins and diamond
            $totalDiamond = $totalPrice * $count;
            $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_diamond_send, $totalDiamond, $user->sub_sender_level);
            $this->updateUserCoins($user->id, $user->di, $userCoins, $totalDiamond, senderLevel: $senderLevel);

            $coinsForReceiverBase = $number * ($giftPrice * $hostPercentage);
            $price = $coinsForReceiverBase * $receiversCount;
            $coinsForReceiver = $coinsForReceiverBase * $count;
            $number = $number * $count;

            $newUserCoin = ($user->di - $userCoins);
            $this->updateCache($userId, $roomId, $receiversIds, $giftId, $data, $number, $price, $coinsForReceiver, $oldUserCoin, $newUserCoin, $total_user_win, $total_count_win);

            if ($room->charizma_status && $coinsForReceiver > 1) {
                dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
            } elseif ($room->lastPk && $coinsForReceiver > 1) {
                dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
            }

            $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);

            $settings = \App\Helpers\CacheHelper::cacheSettings();
            if (gettype($settings) !== 'array') {
                $settings = $settings->pluck('value', 'key')->toArray();
            }
            $roomBoomSettings = $settings['room_boom'] ?? 1;
            $totalHostDiamond = (int) $totalPrice * $hostPercentage;
            if ($roomBoomSettings) {
                (new NewRoomBoomGiftService())->sendGift($room, $totalHostDiamond, $userId);
            } else {
                $tz = getTimezone();
                $todayStart = \Carbon\Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');
                $totalRoomGift = (new RoomService())->getOrCreateTotalRoomGift($room->id, $todayStart);
                $totalRoomGift->increment('current_total', $totalHostDiamond);
            }
            $responseData['total_pk'] = $coinsForReceiver;

            if ($room->type == 'audio') {
                $serviceLevel = new UpgradeRoomLevelServices();
                $serviceLevel->sendGift($room, $totalPrice * $count);
            }

            return $responseData;
        } finally {
            $lock->release();
        }
    }

        public function sendLuckyGift7(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId = $user->id;
        $ownerId = @$data['owner_id'];
        $roomId = @$data['room_id'];
        $giftId = $data['id'];
        $number = $data['num'];
        $count = $data['count'] ?? 1;
        $amountBefore = $user->di;

        $appFeeRate = \App\Models\FairLuckSetting::getAppFeeRate();
        $receiverFeeRate = \App\Models\FairLuckSetting::getReceiverFeeRate();
        $roomPercentage = \App\Models\FairLuckSetting::getOwnerFeeRate();
        $hostPercentage = $receiverFeeRate;
        $total_cashback_percentage = 0;

        $gift = Gift::query()->select(['id', 'name', 'e_name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));

        $giftPrice = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $receiversCount = count($receiversIds);
        $numberOfGift = $number * $receiversCount;
        $totalPrice = $giftPrice * $numberOfGift;
        $totalPriceFull = $totalPrice * $count;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPriceFull) {
            throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
        }

        $lock = $this->acquireUserLock($userId);
        try {
            $user->refresh();
            $userCoins = $user->di;
            $oldUserCoin = $userCoins;
            $amountBefore = $user->di;

            if ($userCoins < $totalPriceFull) {
                throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
            }

            if (isset($ownerId)) {
                $room = Room::withoutAppends()
                    ->where('uid', $ownerId)
                    ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                    ->first();
            } else {
                $room = Room::withoutAppends()
                    ->where('id', $roomId)
                    ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                    ->first();
                $ownerId = $room?->uid;
            }

            if (!$room)
                throw new InvalidArgumentException(__('api_responses.roomNotFound'));

            $roomId = $room->id;

            $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name', 'agency_id'])->get();
            $receiverName = $receivedUsers->first()?->name;
            $receiversCount = $receivedUsers->count();
            $isToRoom = $receiversCount > 1;
            $responseData = $this->getResponseData2($gift, $room, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

            $index = $count;
            $total_user_win = 0;
            $max_single_win = 0;
            $total_count_win = 0;
            $unitPrice = $giftPrice * $number;
            $fairService = app(\App\Services\FairLuck\V7\FairLuckServiceV7::class);
            $throwNumber = 0;

            // Fixed: per-receiver room owner fee (was using total for all receivers before)
            $coinsForOwnerPerReceiver = $unitPrice * $roomPercentage;
            $coinsForOwnerTotal = $coinsForOwnerPerReceiver * $receiversCount;

            $senderBalanceBefore = $user->di;
            $totalWalletsBefore = null;
            $totalWalletsAfter = null;

            $totalPriceFull = $giftPrice * $number * $receiversCount;

            while ($user->di >= $totalPriceFull && $index > 0) {
                $balanceBeforeIteration = $user->di;
                UserCoinLogHelper::logByType(
                    $user->id,
                    -abs($totalPriceFull),
                    $balanceBeforeIteration,
                    UserCoinLogType::LUCKY_GIFT,
                    $gift?->name,
                );

                foreach ($receiversIds as $receiverId) {

                    if ($user->di < $unitPrice) {
                        $index = 0;
                        break;
                    }

                    $throwNumber++;

                    $senderBalanceBeforeHit = $user->di;

                    // Fixed: use per-receiver room fee instead of total
                    $appFee = $unitPrice * $appFeeRate;
                    $receiverFee = $unitPrice * $receiverFeeRate;
                    $netBetAmount = $unitPrice - $appFee - $receiverFee - $coinsForOwnerPerReceiver;

                    try {
                        $result = $fairService->processBet(
                            $user,
                            $gift,
                            $netBetAmount,
                            $unitPrice,
                            $roomId,
                            $receiverId,
                            $appFee,
                            $receiverFee,
                            $senderBalanceBeforeHit,
                            $user->di - $unitPrice
                        );
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('FairLuckServiceV7 processBet FAILED', [
                            'user_id' => $userId,
                            'throw_number' => $throwNumber,
                            'receiver_id' => $receiverId,
                            'error' => $e->getMessage(),
                        ]);
                        $result = null;
                    }

                    $iterationWin = 0;
                    $isWinner = false;
                    $multiplier = 0;
                    $message = null;

                    if ($result) {
                        $isWinner = (bool) ($result->isWinner ?? false);
                        $multiplier = (float) ($result->multiplier ?? 0);
                        $iterationWin = (float) ($result->profitAmount ?? 0);

                        if ($isWinner && $iterationWin > 0) {
                            $user->enableSaving = false;
                            $user->di += $iterationWin;

                            $total_user_win += $iterationWin;
                            $total_count_win++;
                            if ($iterationWin > $max_single_win) {
                                $max_single_win = $iterationWin;
                            }

                            if ($multiplier > 1) {
                                $message = $this->winnerMessage($multiplier);
                            }
                        }

                        if ($totalWalletsBefore === null) {
                            $totalWalletsBefore = $result->wallets_before ?? null;
                        }
                        $totalWalletsAfter = $result->wallets_after ?? null;
                    }

                    $isPopular = $multiplier >= 5;

                    if ($isPopular && $iterationWin > 0) {
                        $this->sendPopularToZegoV2(
                            $userId,
                            $user,
                            $gift,
                            $ownerId,
                            $room,
                            $multiplier,
                            cashbackValue: $iterationWin
                        );
                    }

                    [$commentMessage, $sendMessage] = $this->getSendMessage(
                        $giftPrice,
                        $message,
                        $receiverName,
                        $number,
                        isToRoom: $isToRoom
                    );

                    $senderBalanceAfterHit = (int) ($senderBalanceBeforeHit - $unitPrice + ($iterationWin > 0 ? $iterationWin : 0));

                    $responseData['combo'][] = [
                        'status' => 0,
                        'data' => [
                            'win_coins' => (int) $iterationWin,
                            'is_win' => $isWinner,
                            'is_popular' => $isPopular,
                            'comment_message' => $commentMessage,
                            'winner_comment' => $sendMessage,
                        ],
                        'error_message' => '',
                        'sender_balance_before' => (int) $senderBalanceBeforeHit,
                        'sender_balance_after' => $senderBalanceAfterHit,
                        'wallets_before' => $result?->wallets_before,
                        'wallets_after' => $result?->wallets_after,
                    ];

                    $user->di -= $unitPrice;
                    $total_cashback_percentage += $multiplier;
                }

                $index--;
            }

            if ($total_user_win > 0) {
                UserCoinLogHelper::logByType(
                    $userId,
                    $total_user_win,
                    ($user->di - $total_user_win),
                    UserCoinLogType::CASHBACK,
                    null,
                );
            }

            if ($index > 0) {
                $count -= $index;

                $responseData['combo'][] = [
                    'status' => 1,
                    'data' => null,
                    'error_message' => __('api_responses.insufficient'),
                ];
            }

            $room->session += $coinsForOwnerTotal * $count;
            $room->save();

            $responseData['session'] = $room->session_string;
            $responseData['user_coins'] = $user->di;
            $responseData['gift_num'] = $receiversCount * $number * $count;
            $responseData['total_price'] = $totalPrice;
            $responseData['cashback_percentage'] = $total_cashback_percentage;
            $responseData['total_user_win'] = $total_user_win;
            $responseData['gift_name'] = app()->getLocale() === 'ar' ? $gift->name ?? $gift->e_name : $gift->e_name ?? $gift->name;
            $responseData['summary'] = [
                'total_win' => $total_user_win,
                'max_single_win' => $max_single_win,
                'total_win_count' => $total_count_win,
            ];

            $responseData['balances'] = [
                'sender' => [
                    'before' => (int) $senderBalanceBefore,
                    'after' => (int) $user->di,
                ],
                'wallets' => [
                    'before' => $totalWalletsBefore,
                    'after' => $totalWalletsAfter,
                ],
            ];

            // Update user coins and diamond
            $totalDiamond = $totalPrice * $count;
            $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_diamond_send, $totalDiamond, $user->sub_sender_level);
            $this->updateUserCoins($user->id, $user->di, $userCoins, $totalDiamond, senderLevel: $senderLevel);

            $coinsForReceiverBase = $number * ($giftPrice * $hostPercentage);
            $price = $coinsForReceiverBase * $receiversCount;
            $coinsForReceiver = $coinsForReceiverBase * $count;
            $number = $number * $count;

            $newUserCoin = ($user->di - $userCoins);
            $this->updateCache($userId, $roomId, $receiversIds, $giftId, $data, $number, $price, $coinsForReceiver, $oldUserCoin, $newUserCoin, $total_user_win, $total_count_win);

            if ($room->charizma_status && $coinsForReceiver > 1) {
                dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
            } elseif ($room->lastPk && $coinsForReceiver > 1) {
                dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
            }

            $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);

            $settings = \App\Helpers\CacheHelper::cacheSettings();
            if (gettype($settings) !== 'array') {
                $settings = $settings->pluck('value', 'key')->toArray();
            }
            $roomBoomSettings = $settings['room_boom'] ?? 1;
            $totalHostDiamond = (int) $totalPrice * $hostPercentage;
            if ($roomBoomSettings) {
                (new NewRoomBoomGiftService())->sendGift($room, $totalHostDiamond, $userId);
            } else {
                $tz = getTimezone();
                $todayStart = \Carbon\Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');
                $totalRoomGift = (new RoomService())->getOrCreateTotalRoomGift($room->id, $todayStart);
                $totalRoomGift->increment('current_total', $totalHostDiamond);
            }
            $responseData['total_pk'] = $coinsForReceiver;

            if ($room->type == 'audio') {
                $serviceLevel = new UpgradeRoomLevelServices();
                $serviceLevel->sendGift($room, $totalPrice * $count);
            }

            return $responseData;
        } finally {
            $lock->release();
        }
    }

    public function sendLuckyGift2V2(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId = $user->id;
        $ownerId = @$data['owner_id'];
        $roomId = @$data['room_id'];
        $giftId = $data['id'];
        $number = $data['num'];
        $count = $data['count'] ?? 1;
        $amountBefore = $user->di;
        $appPercentage = getGiftPercentage('app_wallet_lucky_gift') / 10;
        $roomrPercentage = getGiftPercentage('owner_lucky_gift') / 10;
        $hostPercentage = getGiftPercentage('host_lucky_gift') / 10;
        $total_cashback_percentage = 0;


        $gift = Gift::query()->select(['id', 'name', 'e_name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            // ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));

        $giftPrice = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);
        $totalPrice = $giftPrice * $numberOfGift;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPrice) {
            throw new InvalidArgumentException(__('api_responses.insufficient'));
        }

        $lock = $this->acquireUserLock($userId);
        try {
            $user->refresh();
            $userCoins = $user->di;
            $oldUserCoin = $userCoins;
            $amountBefore = $user->di;

            if ($userCoins < $totalPrice) {
                throw new InvalidArgumentException(__('api_responses.insufficient'));
            }


        if (isset($ownerId)) {
            $room = Room::withoutAppends()
                ->where('uid', $ownerId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status,type,total_diamond,level,level_id')
                ->first();
        } else {
            $room = Room::withoutAppends()
                ->where('id', $roomId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status,type,total_diamond,level,level_id')
                ->first();
            $ownerId = $room?->uid;
        }

        if (!$room)
            throw new InvalidArgumentException(__('api_responses.roomNotFound'));

        $roomId = $room->id;

        /// todo check visitors

        [$ownerWallet, $appWallet] = $this->getCoreWallets();
        if (!($appWallet instanceof CoreWallet) || !($ownerWallet instanceof CoreWallet))
            throw new InvalidArgumentException('app dosn\'t resolved ');
        $firstAppWalletCoins = $appWallet->coins;
        $firstOwnerWalletCoins = $ownerWallet->coins;


        $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name', 'agency_id'])->get();
        $receiverName = $receivedUsers->first()?->name;
        $receiversCount = $receivedUsers->count();
        $isToRoom = $receiversCount > 1;



        //        $responseData = $this->getResponseData($gift, $room->microphone, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));
        $responseData = $this->getResponseData2($gift, $room, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

        $index = $count;

        $coinsForReceiver = $number * ($giftPrice * $hostPercentage);
        $price = $coinsForReceiver * $receiversCount;
        $total_user_win = 0;
        $total_count_win = 0;

        UserCoinLogHelper::logByType(
            $user->id,
            -abs($totalPrice),
            $amountBefore,
            UserCoinLogType::LUCKY_GIFT,
            $gift?->name,
        );

        $totalPrice = $giftPrice * $number * $receiversCount;
        $coinsForApp = $totalPrice * $appPercentage;
        $coinsForOwner = $totalPrice * $roomrPercentage;

        while ($user->di >= $totalPrice && $index > 0) {

            $appWallet->coins += $coinsForApp;
            $ownerWallet->coins += $price; //        $appWallet->save();
            $isWinner = $this->is_winner($gift);

            $isPopular = false;
            $totalGiftPrice = $giftPrice * $number;
            $appWalletCoins = $appWallet->coins;
            if ($isWinner && $appWalletCoins > ($totalGiftPrice)) {
                $properties = $gift->luckyGift?->min_percentage;

                $cashback_percentage = $this->getTimesOfPrice($appWalletCoins, $totalGiftPrice, $properties);

                $cashback_value = $cashback_percentage * $giftPrice * $number;
                if ($cashback_percentage > 0) {

                    $user->enableSaving = false;
                    $user->di += $cashback_value;
                    //                $user->save();
                    $appWallet->coins -= $cashback_value;
                    //                $appWallet->save();
                    if ($cashback_percentage > 1) {
                        $message = $this->winnerMessage($cashback_percentage);
                    }
                } else {
                    $isWinner = false;
                }

                //send to zigo this data to show in all rooms if cashback percentage > 20
                $isPopular = $this->isPopular($cashback_percentage);

                if ($isPopular) {

                    $this->sendPopularToZegoV2($userId, $user, $gift, $ownerId, $room, $cashback_percentage, cashbackValue: $cashback_value);
                }
            } else {
                $cashback_percentage = 0;
            }

            [$commentMessage, $sendMessage] =
                $this->getSendMessage($giftPrice, $message ?? null, $receiverName, $number, isToRoom: $isToRoom);

            $responseData['combo'][] = [
                'status' => 0,
                'data' => [
                    'win_coins' => (int) ($totalGiftPrice * $cashback_percentage),
                    'is_win' => $isWinner,
                    'is_popular' => $isPopular,
                    'comment_message' => $commentMessage,
                    'winner_comment' => $sendMessage,
                ],
                'error_message' => '',
            ];

            $user->di -= $totalPrice;
            $index--;
            $message = null;
            $total_user_win += ($totalGiftPrice * $cashback_percentage);
            $total_count_win += 1;
            $total_count_win += 1;
            $total_cashback_percentage += $cashback_percentage;
            $cashback_percentage = 0;
            //            $this->save_data_win_for_user($user->id,$totalGiftPrice,$cashback_percentage);
        }



        if ($total_user_win > 0) {

            UserCoinLogHelper::logByType(
                $userId,
                $total_user_win,
                ($user->di - $total_user_win),
                UserCoinLogType::CASHBACK,
                null,
            );
        }


        if ($index > 0) {
            $count -= $index;
            $responseData['combo'][] = [
                'status' => 1,
                'data' => null,
                'error_message' => __('api_responses.insufficient'),
            ];
        }

        // update room session
        // $room->session      += (int)$gift->price * $number * $count * 0.1;
        $room->session += $coinsForOwner;
        $room->save();

        // add session to response
        $responseData['session'] = $room->session_string;

        //new user coins
        $responseData['user_coins'] = $userCoins;
        $responseData['gift_num'] = $receiversCount * $number * $count;
        $responseData['total_price'] = $totalPrice;
        $responseData['cashback_percentage'] = $total_cashback_percentage;
        $responseData['total_user_win'] = $total_user_win;
        $responseData['gift_name'] = app()->getLocale() === 'ar' ? $gift->name ?? $gift->e_name : $gift->e_name ?? $gift->name;

        //update user coins and diamond and sender level
        $totalDiamond = $totalPrice * $count;
        $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_sender_diamonds, $totalDiamond, $user->sub_sender_level);
        $this->updateUserCoins($user->id, $user->di, $userCoins, $totalDiamond, senderLevel: $senderLevel);


        // update core wallet
        $diffAppWallet = $appWallet->coins - $firstAppWalletCoins;
        $diffOwnerWallet = $ownerWallet->coins - $firstOwnerWalletCoins;
        $this->updateCoreWallet($diffAppWallet, $diffOwnerWallet);


        $coinsForReceiver = $coinsForReceiver * $count;
        $number = $number * $count;

        $newUserCoin = ($user->di - $userCoins);
        $this->updateCache($userId, $roomId, $receiversIds, $giftId, $data, $number, $price, $coinsForReceiver, $oldUserCoin, $newUserCoin, $total_user_win, $total_count_win);

        if ($room->charizma_status && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
        } elseif ($room->lastPk && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
        }

        $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);

        // \Log::info('sendLuckyGift2V2 - Room Type Check', [
        //     'room_id' => $room->id,
        //     'room_type' => $room->type,
        //     'total_diamond' => $room->total_diamond,
        //     'totalPrice' => $totalPrice,
        // ]);

        // Upgrade room level for audio rooms
        if ($room->type == 'audio') {
            $serviceLevel = new UpgradeRoomLevelServices();
            $serviceLevel->sendGift($room, $totalPrice);
        }

        return $responseData;
        } finally {
            $lock->release();
        }
    }

    public function sendLuckyGift2V3(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId = $user->id;
        $ownerId = @$data['owner_id'];
        $roomId = @$data['room_id'];
        $giftId = $data['id'];
        $number = $data['num'];
        $count = $data['count'] ?? 1;
        $amountBefore = $user->di;
        $appPercentage = getGiftPercentage('app_wallet_lucky_gift') / 10;
        $roomrPercentage = getGiftPercentage('owner_lucky_gift') / 10;
        $hostPercentage = getGiftPercentage('host_lucky_gift') / 10;
        $total_cashback_percentage = 0;


        $gift = Gift::query()->select(['id', 'name', 'e_name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));

        $giftPrice = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);
        $totalPrice = $giftPrice * $numberOfGift;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPrice) {
            throw new InvalidArgumentException(__('api_responses.insufficient'));
        }

        $lock = $this->acquireUserLock($userId);
        try {
            $user->refresh();
            $userCoins = $user->di;
            $oldUserCoin = $userCoins;
            $amountBefore = $user->di;

            if ($userCoins < $totalPrice) {
                throw new InvalidArgumentException(__('api_responses.insufficient'));
            }

        if (isset($ownerId)) {
            $room = Room::withoutAppends()
                ->where('uid', $ownerId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
        } else {
            $room = Room::withoutAppends()
                ->where('id', $roomId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
            $ownerId = $room?->uid;
        }

        if (!$room)
            throw new InvalidArgumentException(__('api_responses.roomNotFound'));

        $roomId = $room->id;



        /// todo check visitors

        [$ownerWallet, $appWallet] = $this->getCoreWallets();
        if (!($appWallet instanceof CoreWallet) || !($ownerWallet instanceof CoreWallet))
            throw new InvalidArgumentException('app dosn\'t resolved ');
        $firstAppWalletCoins = $appWallet->coins;
        $firstOwnerWalletCoins = $ownerWallet->coins;


        $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name', 'agency_id'])->get();
        $receiverName = $receivedUsers->first()?->name;
        $receiversCount = $receivedUsers->count();
        $isToRoom = $receiversCount > 1;



        //        $responseData = $this->getResponseData($gift, $room->microphone, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));
        $responseData = $this->getResponseData2($gift, $room, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

        $index = $count;

        $coinsForReceiver = $number * ($giftPrice * $hostPercentage);
        $price = $coinsForReceiver * $receiversCount;
        $total_user_win = 0;
        $max_single_win = 0;
        $total_count_win = 0;

        $totalPrice = $giftPrice * $number * $receiversCount;
        $coinsForApp = $totalPrice * $appPercentage;
        $coinsForOwner = $totalPrice * $roomrPercentage;

        while ($user->di >= $totalPrice && $index > 0) {
            $balanceBeforeIteration = $user->di;
            UserCoinLogHelper::logByType(
                $user->id,
                -abs($totalPrice),
                $balanceBeforeIteration,
                UserCoinLogType::LUCKY_GIFT,
                $gift?->name,
            );

            $appWallet->coins += $coinsForApp;
            $ownerWallet->coins += $price; //        $appWallet->save();

            $iterationTotalWin = 0;
            $iterationPopular = false;
            $iterationMaxCashback = 0;
            $message = null;
            $unitPrice = $giftPrice * $number;

            foreach ($receiversIds as $receiverId) {
                $isWinner = $this->is_winner($gift);
                if ($isWinner) {
                    $appWalletCoins = $appWallet->coins;
                    if ($appWalletCoins > $unitPrice) {
                        $properties = $gift->luckyGift?->min_percentage;
                        $cashback_percentage = $this->getTimesOfPrice($appWalletCoins, $unitPrice, $properties);
                        $cashback_value = $cashback_percentage * $unitPrice;

                        if ($cashback_percentage > 0) {
                            $user->enableSaving = false;
                            $user->di += $cashback_value;
                            $appWallet->coins -= $cashback_value;

                            $iterationTotalWin += $cashback_value;
                            $total_user_win += $cashback_value;
                            $total_count_win++;
                            $iterationMaxCashback = max($iterationMaxCashback, $cashback_percentage);

                            if ($this->isPopular($cashback_percentage)) {
                                $iterationPopular = true;
                            }
                        }
                    }
                }
            }

            if ($iterationTotalWin > 0) {
                $displayMultiplier = $this->resolveWinnerMultiplier($iterationTotalWin, $unitPrice, $iterationMaxCashback);
                $message = $displayMultiplier > 1 ? $this->winnerMessage($displayMultiplier) : null;
            }

            if ($iterationPopular) {
                $this->sendPopularToZegoV2($userId, $user, $gift, $ownerId, $room, $iterationMaxCashback, cashbackValue: $iterationTotalWin);
            }

            [$commentMessage, $sendMessage] =
                $this->getSendMessage($giftPrice, $message ?? null, $receiverName, $number, isToRoom: $isToRoom);

            $responseData['combo'][] = [
                'status' => 0,
                'data' => [
                    'win_coins' => $iterationTotalWin,
                    'is_win' => $iterationTotalWin > 0,
                    'is_popular' => $iterationPopular,
                    'comment_message' => $commentMessage,
                    'winner_comment' => $sendMessage,
                ],
                'error_message' => '',
            ];

            $user->di -= $totalPrice;
            $index--;
            $total_cashback_percentage += $iterationMaxCashback;
        }



        if ($total_user_win > 0) {

            UserCoinLogHelper::logByType(
                $userId,
                $total_user_win,
                ($user->di - $total_user_win),
                UserCoinLogType::CASHBACK,
                null,
            );
        }


        if ($index > 0) {
            $count -= $index;
            $responseData['combo'][] = [
                'status' => 1,
                'data' => null,
                'error_message' => __('api_responses.insufficient'),
            ];
        }

        // update room session
        // $room->session      += (int)$gift->price * $number * $count * 0.1;
        $room->session += $coinsForOwner * $count;
        $room->save();

        // add session to response
        $responseData['session'] = $room->session_string;

        //new user coins
        $responseData['user_coins'] = $userCoins;
        $responseData['gift_num'] = $receiversCount * $number * $count;
        $responseData['total_price'] = $totalPrice;
        $responseData['cashback_percentage'] = $total_cashback_percentage;
        $responseData['total_user_win'] = $total_user_win;
        $responseData['gift_name'] = app()->getLocale() === 'ar' ? $gift->name ?? $gift->e_name : $gift->e_name ?? $gift->name;
        $responseData['summary'] = [
            'total_win' => $total_user_win,
            'max_single_win' => $max_single_win,
            'total_win_count' => $total_count_win,
        ];

        //update user coins and diamond and sender level
        $totalDiamond = $totalPrice * $count;
        $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_sender_diamonds, $totalDiamond, $user->sub_sender_level);
        $this->updateUserCoins($user->id, $user->di, $userCoins, $totalDiamond, senderLevel: $senderLevel);


        // update core wallet
        $diffAppWallet = $appWallet->coins - $firstAppWalletCoins;
        $diffOwnerWallet = $ownerWallet->coins - $firstOwnerWalletCoins;
        $this->updateCoreWallet($diffAppWallet, $diffOwnerWallet);


        $coinsForReceiver = $coinsForReceiver * $count;
        $number = $number * $count;

        $newUserCoin = ($user->di - $userCoins);
        $this->updateCache($userId, $roomId, $receiversIds, $giftId, $data, $number, $price, $coinsForReceiver, $oldUserCoin, $newUserCoin, $total_user_win, $total_count_win);

        if ($room->charizma_status && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
        } elseif ($room->lastPk && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
        }

        $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);

        // Update total_room_gifts table
        $settings = CacheHelper::cacheSettings();
        if (gettype($settings) !== 'array') {
            $settings = $settings->pluck('value', 'key')->toArray();
        }
        $roomBoomSettings = $settings['room_boom'] ?? 1;
        $totalHostDiamond = (int) $totalPrice * $hostPercentage;
        if ($roomBoomSettings) {

            (new NewRoomBoomGiftService())->sendGift($room, $totalHostDiamond, $userId);
        } else {
            $tz = getTimezone();
            $todayStart = Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');
            $totalRoomGift = (new RoomService())->getOrCreateTotalRoomGift($room->id, $todayStart);
            $totalRoomGift->increment('current_total', $totalHostDiamond);
        }

        if ($room->type == 'audio') {
            $serviceLevel = new UpgradeRoomLevelServices();
            $serviceLevel->sendGift($room, $totalPrice * $count);
        }

        return $responseData;
        } finally {
            $lock->release();
        }
    }
    public function sendLuckyGift3(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId = $user->id;
        $ownerId = $data['owner_id'];
        $giftId = $data['id'];
        $number = $data['num'];
        $count = $data['count'] ?? 1;

        $gift = Gift::query()->select(['id', 'name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));

        // update gift count
        (new LuckyStrategyService())->getUpdateGiftCountForCategory($gift);

        $giftPrice = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);
        $totalPrice = $giftPrice * $numberOfGift;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPrice) {
            throw new InvalidArgumentException(__('api_responses.insufficient'));
        }

        $room = Room::withoutAppends()
            ->where('uid', $ownerId)
            ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status')
            ->first();
        if (!$room)
            throw new InvalidArgumentException(__('api_responses.roomNotFound'));

        $roomId = $room->id;


        /// todo check visitors

        [$ownerWallet, $appWallet] = $this->getCoreWallets();

        if (!($appWallet instanceof CoreWallet) || !($ownerWallet instanceof CoreWallet))
            throw new InvalidArgumentException('app dosn\'t resolved ');
        $firstAppWalletCoins = $appWallet->coins;
        $firstOwnerWalletCoins = $ownerWallet->coins;


        $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name'])->get();
        $receiverName = $receivedUsers->first()->name;
        $receiversCount = $receivedUsers->count();
        $isToRoom = $receiversCount > 1;



        $responseData = $this->getResponseData($gift, $room->microphone, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

        $index = $count;

        $coinsForReceiver = $number * ($giftPrice * 0.1);
        $price = $coinsForReceiver * $receiversCount;
        $total_user_win = 0;
        $total_count_win = 0;
        while ($user->di >= $totalPrice && $index > 0) {

            $appWallet->coins += $price * 8;
            $ownerWallet->coins += $price; //        $appWallet->save();
            //        $ownerWallet->save();
            $isWinner = $this->is_winner($gift);
            $isPopular = false;
            $totalGiftPrice = $giftPrice * $number;
            $appWalletCoins = $appWallet->coins;
            if ($isWinner && $appWalletCoins > ($totalGiftPrice)) {
                $properties = $gift->luckyGift?->min_percentage;

                $cashback_percentage = $this->getTimesOfPrice($appWalletCoins, $totalGiftPrice, $properties);

                $cashback_value = $cashback_percentage * $giftPrice * $number;
                if ($cashback_percentage > 0) {
                    $user->enableSaving = false;
                    $user->di += $cashback_value;
                    //                $user->save();
                    $appWallet->coins -= $cashback_value;
                    //                $appWallet->save();
                    if ($cashback_percentage > 1) {
                        $message = $this->winnerMessage($cashback_percentage);
                    }
                } else {
                    $isWinner = false;
                }

                //send to zigo this data to show in all rooms if cashback percentage > 20
                $isPopular = $this->isPopular($cashback_percentage);
                if ($isPopular) {
                    $this->sendPopularToZego($userId, $user, $gift, $ownerId, $room, $cashback_percentage);
                }
            } else {
                $cashback_percentage = 0;
            }

            [$commentMessage, $sendMessage] =
                $this->getSendMessage($giftPrice, $message ?? null, $receiverName, $number, isToRoom: $isToRoom);
            // UPDATE user win
            (new LuckyStrategyService())->getUpdateUserStatistic($user, $totalGiftPrice, (int) ($totalGiftPrice * $cashback_percentage));
            $responseData['combo'][] = [
                'status' => 0,
                'data' => [
                    'win_coins' => (int) ($totalGiftPrice * $cashback_percentage),
                    'is_win' => $isWinner,
                    'is_popular' => $isPopular,
                    'comment_message' => $commentMessage,
                    'winner_comment' => $sendMessage,
                ],
                'error_message' => '',
            ];
            $user->di -= $totalPrice;
            $index--;
            $message = null;
            $total_user_win += ($totalGiftPrice * $cashback_percentage);
            $total_count_win += 1;
            $cashback_percentage = 0;
            //            $this->save_data_win_for_user($user->id,$totalGiftPrice,$cashback_percentage);
        }

        if ($index > 0) {
            $count -= $index;
            $responseData['combo'][] = [
                'status' => 1,
                'data' => null,
                'error_message' => __('api_responses.insufficient'),
            ];
        }

        // update room session
        $room->session += (int) $gift->price * $number * $count * 0.1;
        $room->save();

        // add session to response
        $responseData['session'] = $room->session_string;

        //new user coins
        $responseData['user_coins'] = $userCoins;

        //update user coins and diamond and sender level
        $totalDiamond = $totalPrice * $count;
        $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_sender_diamonds, $totalDiamond, $user->sub_sender_level);
        $this->updateUserCoins($user->id, $user->di, $userCoins, $totalDiamond, senderLevel: $senderLevel);


        // update core wallet
        $diffAppWallet = $appWallet->coins - $firstAppWalletCoins;
        $diffOwnerWallet = $ownerWallet->coins - $firstOwnerWalletCoins;
        $this->updateCoreWallet($diffAppWallet, $diffOwnerWallet);


        $coinsForReceiver = $coinsForReceiver * $count;
        $number = $number * $count;

        $newUserCoin = ($user->di - $userCoins);
        $this->updateCache($userId, $roomId, $receiversIds, $giftId, $data, $number, $price, $coinsForReceiver, $oldUserCoin, $newUserCoin, $total_user_win, $total_count_win);

        if ($room->charizma_status && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
        } elseif ($room->lastPk && $coinsForReceiver > 1) {
            dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
        }

        $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);


        return $responseData;
    }

    /**
     * @param mixed $isToRoom
     * @param string|null $receiverName
     * @return string
     */
    public function getReceiverName(mixed $isToRoom, ?string $receiverName): string
    {
        return $isToRoom ? 'الغرفة' : ($receiverName ?? '');
    }


    private function getSendMessage($giftPrice, ?string $message, ?string $receiverName, $number, $isToRoom = false)
    {
        $commentMessage = $this->getCommentMessage($isToRoom, $receiverName, $number, $giftPrice);

        return [$commentMessage, (!isset($message) ? '' : $message)];
    }

    private function getResponseData($gift, $roomMics, $user, $receiversIds, $receiverName)
    {

        $positions = [];
        $userInMic = [];
        $mics = explode(',', $roomMics);

        foreach ($mics as $key => $value) {
            $founded = in_array($value, $receiversIds);

            if ($founded) {
                $userInMic[] = $value;
                $positions[] = $key;
            }
        }

        if (count(array_diff($receiversIds, $userInMic)) > 0) {
            $positions[] = -1;
        }

        return [
            'gift_image' => $gift->img,
            'receiver_name' => $receiverName,
            'receivers_ids' => $receiversIds,
            'sender_id' => $user->id ?? 0,
            'sender_name' => $user->name ?? '',
            'sender_img' => $user->profile->avatar ?? '',
            'position' => $positions,
            'combo' => [],
        ];
    }

    private function getResponseData2($gift, $room, $user, $receiversIds, $receiverName)
    {
        $microphones = $room->microphones ?? collect();



        $positions = $microphones
            ->filter(fn($mic) => in_array($mic->user_id, $receiversIds))
            ->pluck('position')
            ->values()
            ->all();

        $missingReceivers = array_diff($receiversIds, $microphones->pluck('user_id')->all());


        if (!empty($missingReceivers)) {
            $positions[] = -1;
        }

        return [
            'gift_image' => $gift->img,
            'receiver_name' => $receiverName,
            'receivers_ids' => $receiversIds,
            'sender_id' => $user->id ?? 0,
            'sender_name' => $user->name ?? '',
            'sender_img' => $user->profile->avatar ?? '',
            'position' => $positions,
            'combo' => [],
        ];
    }

    /**
     * @return mixed
     */
    public function getCoreWallets(): array
    {
        $collection = CoreWallet::query()
            ->whereIn('name', ['app_wallet', 'owner_wallet'])
            ->get();

        $wallets = $collection->sortBy('id')->values();

        $ownerWallet = $wallets->firstWhere('name', 'owner_wallet') ?? null;
        $appWallet = $wallets->firstWhere('name', 'app_wallet') ?? null;
        // $ownerWallet = $wallets[1] ?? null;
        // $appWallet = $wallets[0] ?? null;

        return [$ownerWallet, $appWallet];
    }

    /**
     * @param mixed $userId
     * @param $roomId
     * @param array $receiversIds
     * @param mixed $giftId
     * @param mixed $data
     * @param mixed $number
     * @param float $price
     * @param float $coinsForReceiver
     * @return void
     */
    public function updateCache(mixed $userId, $roomId, array $receiversIds, mixed $giftId, mixed $data, mixed $number, float $price, float $coinsForReceiver, float $userCoinsBefore = 0, float $userCoinsAfter = 0, int $total_user_win = 0, int $total_count_win = 0): void
    {
        $key = 'luckyGift_' . $userId . '_' . $roomId . '_' . implode($receiversIds) . '_' . $giftId;
        $data = RedisService::getUnSerialize($key);
        if ($data) {
            if (isset($data['number'])) {
                $data['number'] += $number;
                $data['total_num_win'] += $total_count_win;
                $data['total_user_win'] += $total_user_win;
                $data['user_coin_befor'] = $userCoinsBefore;
                $data['user_coin_after'] = $userCoinsAfter;
                RedisService::updateUnSerialize($key, $data);
            }
        } else {
            $data = [
                'number' => $number,
                'room_id' => $roomId,
                'giftId' => $giftId,
                'price' => $price,
                'receiversIds' => $receiversIds,
                'coins_for_receiver' => $coinsForReceiver,
                'user_id' => $userId,
                'user_coin_befor' => $userCoinsBefore,
                'user_coin_after' => $userCoinsAfter,
                'total_num_win' => $total_count_win,
                'total_user_win' => $total_user_win
            ];
            RedisService::updateUnSerialize($key, $data);
        }
    }

    /**
     * @param mixed $cashback_percentage
     * @return bool
     */
    public function isPopular(mixed $cashback_percentage): bool
    {
        return $cashback_percentage >= 5;
    }

    /**
     * @param mixed $userId
     * @param User $user
     * @param Gift $gift
     * @param mixed $ownerId
     * @param Room $room
     * @param mixed $cashback_percentage
     * @return void
     */
    public function sendPopularToZego(mixed $userId, User $user, Gift $gift, mixed $ownerId, Room $room, mixed $cashback_percentage, $cashbackValue = 0): void
    {
        $zigoData = [
            'user_id' => $userId,
            'user_image' => @$user->profile->avatar ?? '',
            'gift_image' => @$gift->img ?? '',
            'owner_id' => $ownerId,
            'user_name' => $user->name ?? '',
            'room_id' => $room->id,
            'percentage' => $cashback_percentage,
            'is_room_pass' => ($room->room_pass != null && $room->room_pass != ''),
            'gift_price' => $gift->price,
            'cache_value' => ceil($cashbackValue ?? 0.0),
            'room_name' => $room->room_name ?: '',
            'room_cover' => $room->room_cover ?? '',
            'room_background' => $room->final_room_image ?? '',
            'room_mode' => $room->mode,
            'room_uuid' => $room->owner?->uuid ?: 0,
            'room_owner_id' => $room->uid ?: 0,
            'is_password' => (bool) (@$room->room_pass),
            'room_type' => (@$room->type),

        ];
        $this->sendToZegoLuckyGift($zigoData);
    }
    public function sendPopularToZegoV2(mixed $userId, User $user, Gift $gift, mixed $ownerId, Room $room, mixed $cashback_percentage, $cashbackValue = 0): void
    {
        $zigoData = [
            'user_id' => $userId,
            'user_image' => @$user->profile->avatar ?? '',
            'gift_image' => @$gift->img ?? '',
            'owner_id' => $ownerId,
            'user_name' => $user->name ?? '',
            'room_id' => $room->id,
            'percentage' => $cashback_percentage,
            'is_room_pass' => ($room->room_pass != null && $room->room_pass != ''),
            'gift_price' => $gift->price,
            'cache_value' => ceil($cashbackValue ?? 0.0),
            'room_name' => $room->room_name ?: '',
            'room_cover' => $room->room_cover ?? '',
            'room_background' => $room->final_room_image ?? '',
            'room_mode' => $room->mode,
            'room_uuid' => $room->owner?->uuid ?: 0,
            'room_owner_id' => $room->uid ?: 0,
            'is_password' => (bool) (@$room->room_pass),
            'room_type' => (@$room->type),

        ];
        $this->sendToZegoLuckyGiftV2($zigoData);
    }

    /**
     * @return Collection
     */
    public function getProbabilityTimes(): Collection
    {
        return collect([
            [5, 10, 20],
            [50, 100],
            [250, 500, 1000]
        ]);
    }

    /**
     * @param mixed $appWalletCoins
     * @param float|int $totalGiftPrice
     * @param mixed $properties
     * @return int|mixed
     */
    public function getTimesOfPrice(mixed $appWalletCoins, float|int $totalGiftPrice, mixed $properties): mixed
    {
        $valueTimes = floor($appWalletCoins / ($totalGiftPrice));
        if ($valueTimes == 0)
            return 0;
        $times = min($valueTimes, 1010);
        $probability = $this->getProbabilityTimes();


        $cashback_percentage = $properties ? $valueTimes : rand(1, $times);
        $properties1 = $properties ? explode(',', $properties) : null;
        $cashback_percentage = $this->getRandomDuplicate($probability, $cashback_percentage, $properties1);
        return $cashback_percentage;
    }

    public function resolveWinnerMultiplier(float|int $winCoins, float|int $unitPrice, float|int $fallbackMultiplier): float|int
    {
        if ($unitPrice <= 0) {
            return $fallbackMultiplier;
        }

        $calculatedMultiplier = $winCoins / $unitPrice;

        if ($calculatedMultiplier > 1) {
            $roundedMultiplier = round($calculatedMultiplier, 2);
            $roundedInt = round($roundedMultiplier);

            if (abs($roundedMultiplier - $roundedInt) < 0.01) {
                return (int) $roundedInt;
            }

            return $roundedMultiplier;
        }

        return $fallbackMultiplier;
    }

    /**
     * @param mixed $cashback_percentage
     * @return string
     */
    public function winnerMessage(mixed $cashback_percentage): string
    {
        return "مبروووك .. كسبت " . $cashback_percentage . " ضعف قيمة الهدية";
    }

    /**
     * @param mixed $diffAppWallet
     * @param mixed $diffOwnerWallet
     * @return void
     */
    public function updateCoreWallet(mixed $diffAppWallet, mixed $diffOwnerWallet): void
    {


        $sql = '
        UPDATE core_wallets
        SET coins = CASE
            WHEN name = "owner_wallet" THEN coins + :owner_wallet
            WHEN name = "app_wallet" THEN coins + :app_wallet
        END
        WHERE name IN ("owner_wallet", "app_wallet")
            ';

        \DB::update($sql, [
            'owner_wallet' => $diffOwnerWallet,
            'app_wallet' => $diffAppWallet,
        ]);
    }

    /**
     * @param mixed $di
     * @param mixed $userCoins
     * @return bool
     */
    public function updateUserCoins(int $userId, mixed $currentDi, mixed $userCoins, int $totalDiamond, $senderLevel = null): bool
    {
        return \DB::transaction(function () use ($userId, $currentDi, $userCoins, $totalDiamond, $senderLevel) {
            $user = \DB::table('users')
                ->where('id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$user) {
                return false;
            }

            $diDifference = $currentDi - $userCoins;
            $newBalance = $user->di + $diDifference;

            if ($newBalance < 0) {
                Log::warning('updateUserCoins: rejected negative balance', [
                    'user_id' => $userId,
                    'current_db_di' => $user->di,
                    'stale_start_di' => $userCoins,
                    'computed_end_di' => $currentDi,
                    'delta' => $diDifference,
                    'would_be' => $newBalance,
                ]);
                return false;
            }

            $updateData = [
                'di' => $newBalance,
                'total_diamond_send' => $user->total_diamond_send + $totalDiamond,
            ];

            if ($senderLevel !== null) {
                $updateData['sender_level'] = $senderLevel;
            }
            \DB::table('users')->where('id', $userId)->update($updateData);
            return true;
        });
    }


    /**
     * @param mixed $isToRoom
     * @param string|null $receiverName
     * @param $number
     * @param $giftPrice
     * @return string
     */
    public function getCommentMessage(mixed $isToRoom, ?string $receiverName, $number, $giftPrice): string
    {
        $to = $this->getReceiverName($isToRoom, $receiverName);

        return "{$number} x ارسل هدية حظ " . " قيمتها {$giftPrice} " . " الى {$to}";
    }
}
