<?php

namespace App\Services\Gifts;

use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Exceptions\NotInfMoneyException;
use App\Facades\RedisService;
use App\Helpers\Common;
use App\Models\CoreWallet;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Traits\Gifts\LuckyGiftProbability;
use App\Traits\Gifts\WinLuckyGift;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class LuckyGiftService
{

    use LuckyGiftProbability;
    use WinLuckyGift;

    private UpdateUserWhenSendGift $updateUserWhenSendGift;

    public function send($data) {}

    public function sendLuckyGift2(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId   = $user->id;
        $ownerId  = $data['owner_id'];
        $giftId   = $data['id'];
        $number   = $data['num'];
        $count    = $data['count'] ?? 1;

        $gift = Gift::query()->select(['id', 'name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift) return Common::apiResponse(0, 'api_responses.giftNotFound');

        $giftPrice       = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);
        $totalPrice   = $giftPrice * $numberOfGift;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPrice) {
            throw  new InvalidArgumentException(__('api_responses.insufficient'));
        }

        $room = Room::withoutAppends()
            ->where('uid', $ownerId)
            ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status')
            ->first();
        if (!$room) return Common::apiResponse(0, __('api_responses.roomNotFound'));

        $roomId   = $room->id;


        /// todo check visitors

        [$ownerWallet, $appWallet]      = $this->getCoreWallets();

        if (!($appWallet instanceof CoreWallet) || !($ownerWallet instanceof CoreWallet)) throw new InvalidArgumentException('app dosn\'t resolved ');
        $firstAppWalletCoins = $appWallet->coins;
        $firstOwnerWalletCoins = $ownerWallet->coins;


        $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name'])->get();
        $receiverName = $receivedUsers->first()->name;
        $receiversCount  = $receivedUsers->count();
        $isToRoom      = $receiversCount > 1;



        $responseData = $this->getResponseData($gift, $room->microphone, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

        $index = $count;

        $coinsForReceiver   = $number * ($giftPrice * 0.1);
        $price              = $coinsForReceiver * $receiversCount;
        $total_user_win  = 0;
        $total_count_win = 0;
        while ($user->di >= $totalPrice && $index > 0) {

            $appWallet->coins   += $price * 8;
            $ownerWallet->coins += $price; //        $appWallet->save();
            //        $ownerWallet->save();
            $isWinner       = $this->is_winner($gift);
            $isPopular      = false;
            $totalGiftPrice = $giftPrice * $number;
            $appWalletCoins = $appWallet->coins;
            if ($isWinner && $appWalletCoins > ($totalGiftPrice)) {
                $properties = $gift->luckyGift?->min_percentage;

                $cashback_percentage = $this->getTimesOfPrice($appWalletCoins, $totalGiftPrice, $properties);

                $cashback_value = $cashback_percentage * $giftPrice * $number;
                if ($cashback_percentage > 0) {
                    $user->enableSaving = false;
                    $user->di           += $cashback_value;
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

            $responseData['combo'][] = [
                'status'        => 0,
                'data'          => [
                    'win_coins'       => (int)($totalGiftPrice * $cashback_percentage),
                    'is_win'          => $isWinner,
                    'is_popular'      => $isPopular,
                    'comment_message' => $commentMessage,
                    'winner_comment'  => $sendMessage,
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
            $responseData['combo'][] =   [
                'status'          => 1,
                'data' => null,
                'error_message'   => __('api_responses.insufficient'),
            ];
        }

        // update room session
        $room->session      += (int)$gift->price * $number * $count * 0.1;
        $room->save();

        // add session to response
        $responseData['session'] = $room->session_string;

        //new user coins
        $responseData['user_coins'] = $userCoins;
        $responseData['gift_num'] = $receiversCount * $number * $count;

        //update user coins and diamond and sender level
        $totalDiamond           = $totalPrice * $count;
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


        return  $responseData;
    }

    public function sendLuckyGift3(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $this->updateUserWhenSendGift = $updateUserWhenSendGift;
        $userId   = $user->id;
        $ownerId  = $data['owner_id'];
        $giftId   = $data['id'];
        $number   = $data['num'];
        $count    = $data['count'] ?? 1;

        $gift = Gift::query()->select(['id', 'name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('type', 6)
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift) return Common::apiResponse(0, 'api_responses.giftNotFound');

        // update gift count
        (new LuckyStrategyService())->getUpdateGiftCountForCategory($gift);

        $giftPrice       = $gift->price;
        $receiversIds = explode(',', $data['toUid']);
        $numberOfGift = $number * count($receiversIds);
        $totalPrice   = $giftPrice * $numberOfGift;

        $userCoins = $user->di;
        $oldUserCoin = $userCoins;

        if ($userCoins < $totalPrice) {
            throw  new InvalidArgumentException(__('api_responses.insufficient'));
        }

        $room = Room::withoutAppends()
            ->where('uid', $ownerId)
            ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status')
            ->first();
        if (!$room) return Common::apiResponse(0, __('api_responses.roomNotFound'));

        $roomId   = $room->id;


        /// todo check visitors

        [$ownerWallet, $appWallet]      = $this->getCoreWallets();

        if (!($appWallet instanceof CoreWallet) || !($ownerWallet instanceof CoreWallet)) throw new InvalidArgumentException('app dosn\'t resolved ');
        $firstAppWalletCoins = $appWallet->coins;
        $firstOwnerWalletCoins = $ownerWallet->coins;


        $receivedUsers = User::whereIn('id', $receiversIds)->select(['id', 'name'])->get();
        $receiverName = $receivedUsers->first()->name;
        $receiversCount  = $receivedUsers->count();
        $isToRoom      = $receiversCount > 1;



        $responseData = $this->getResponseData($gift, $room->microphone, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

        $index = $count;

        $coinsForReceiver   = $number * ($giftPrice * 0.1);
        $price              = $coinsForReceiver * $receiversCount;
        $total_user_win  = 0;
        $total_count_win = 0;
        while ($user->di >= $totalPrice && $index > 0) {

            $appWallet->coins   += $price * 8;
            $ownerWallet->coins += $price; //        $appWallet->save();
            //        $ownerWallet->save();
            $isWinner       = $this->is_winner($gift);
            $isPopular      = false;
            $totalGiftPrice = $giftPrice * $number;
            $appWalletCoins = $appWallet->coins;
            if ($isWinner && $appWalletCoins > ($totalGiftPrice)) {
                $properties = $gift->luckyGift?->min_percentage;

                $cashback_percentage = $this->getTimesOfPrice($appWalletCoins, $totalGiftPrice, $properties);

                $cashback_value = $cashback_percentage * $giftPrice * $number;
                if ($cashback_percentage > 0) {
                    $user->enableSaving = false;
                    $user->di           += $cashback_value;
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
            (new LuckyStrategyService())->getUpdateUserStatistic($user, $totalGiftPrice, (int)($totalGiftPrice * $cashback_percentage));
            $responseData['combo'][] = [
                'status'        => 0,
                'data'          => [
                    'win_coins'       => (int)($totalGiftPrice * $cashback_percentage),
                    'is_win'          => $isWinner,
                    'is_popular'      => $isPopular,
                    'comment_message' => $commentMessage,
                    'winner_comment'  => $sendMessage,
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
            $responseData['combo'][] =   [
                'status'          => 1,
                'data' => null,
                'error_message'   => __('api_responses.insufficient'),
            ];
        }

        // update room session
        $room->session      += (int)$gift->price * $number * $count * 0.1;
        $room->save();

        // add session to response
        $responseData['session'] = $room->session_string;

        //new user coins
        $responseData['user_coins'] = $userCoins;

        //update user coins and diamond and sender level
        $totalDiamond           = $totalPrice * $count;
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


        return  $responseData;
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
        $mics      = explode(',', $roomMics);

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
            'gift_image'      => $gift->img,
            'receiver_name'   => $receiverName,
            'sender_id'       => $user->id ?? 0,
            'sender_name'     => $user->name ?? '',
            'sender_img'      => $user->profile->avatar ?? '',
            'position'        => $positions,
            'combo' => [],
        ];
    }

    /**
     * @return mixed
     */
    public function getCoreWallets(): array
    {
        $wallets = CoreWallet::query()
            ->whereIn('name', ['owner_wallet', 'app_wallet'])
            ->get()
            ->keyBy('name');

        $ownerWallet = $wallets['owner_wallet'] ?? null;
        $appWallet = $wallets['app_wallet'] ?? null;

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
        $key  = 'luckyGift_' . $userId . '_' . $roomId . '_' . implode($receiversIds) . '_' . $giftId;
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
                'number'          => $number,
                'room_id' => $roomId,
                'giftId' => $giftId,
                'price' => $price,
                'receiversIds'    => $receiversIds,
                'coins_for_receiver' => $coinsForReceiver,
                'user_id' => $userId,
                'user_coin_befor' => $userCoinsBefore,
                'user_coin_after' => $userCoinsAfter,
                'total_num_win' => $total_count_win,
                'total_user_win'  => $total_user_win
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
        return $cashback_percentage >= 250;
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
    public function sendPopularToZego(mixed $userId, User $user, Gift $gift, mixed $ownerId, Room $room, mixed $cashback_percentage): void
    {
        $zigoData = [
            'user_id'      => $userId,
            'user_image'   => @$user->avatar->image ?? '',
            'gift_image'   => @$gift->img ?? '',
            'owner_id'     => $ownerId,
            'user_name'    => $user->name ?? '',
            'room_id'      => $room->id,
            'percentage'   => $cashback_percentage,
            'is_room_pass' => ($room->room_pass != null && $room->room_pass != '')

        ];
        $this->sendToZegoLuckyGift($zigoData);
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
        $valueTimes  = floor($appWalletCoins / ($totalGiftPrice));
        if ($valueTimes == 0) return 0;
        $times       = min($valueTimes, 1010);
        $probability = $this->getProbabilityTimes();


        $cashback_percentage = $properties ? $valueTimes : rand(1, $times);
        $properties1         = $properties ? explode(',', $properties) : null;
        Log::channel('lucky_gift')->info($properties);
        $cashback_percentage = $this->getRandomDuplicate($probability, $cashback_percentage, $properties1);
        return $cashback_percentage;
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
        \DB::table('core_wallets')->setBindings([$diffOwnerWallet, $diffAppWallet])->whereIn('id', [1, 2])->update([
            'coins' => \DB::raw('CASE WHEN id = 2 THEN coins + ? WHEN id = 1 THEN coins + ? END'),
        ]);
    }

    /**
     * @param mixed $di
     * @param mixed $userCoins
     * @return void
     */
    public function updateUserCoins(int $userId, mixed $di, mixed $userCoins, int $toalDiamond, $senderLevel = null): void
    {
        $values = [
            'di'                 => \DB::raw('di + ' . ($di - $userCoins)),
            'total_diamond_send' => \DB::raw('total_diamond_send + ' . $toalDiamond)
        ];
        if ($senderLevel) {
            $values['sender_level'] = $senderLevel;
        }
        \DB::table('users')->where('id', $userId)->update($values);
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
