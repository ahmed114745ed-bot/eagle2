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
use Illuminate\Contracts\Cache\LockTimeoutException;

class LuckyGiftService
{

    use LuckyGiftProbability;
    use WinLuckyGift;

    private UpdateUserWhenSendGift $updateUserWhenSendGift;

    public function send($data)
    {
    }

    private function acquireUserLock(int $userId, int $timeoutSeconds = 1): \Illuminate\Contracts\Cache\Lock
    {
         $lock = Cache::lock("lucky_gift_lock:user:{$userId}", $timeoutSeconds);

         if (!$lock->get()) {
             Log::channel('lucky_gift')->warning('Lock timeout - gift already in progress', [
                 'user_id' => $userId,
                 'lock_key' => "lucky_gift_lock:user:{$userId}",
             ]);
             throw new InvalidArgumentException(__('api_responses.gift_in_progress'));
         }

         return $lock;
     }

    public function sendLuckyGiftV2(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift): array
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
        $hostPercentage = $receiverFeeRate;
        $total_cashback_percentage = 0;
        

        // ========================================================================
        // CHUNKING STRATEGY FOR LARGE COUNTS
        // ========================================================================
        // If count > 10, split into chunks to prevent worker blocking
        // First chunk (5-10) processed synchronously, rest queued
        $chunkSize = 10;
        $firstChunkSize = min($count, $chunkSize);
        $remainingCount = $count - $firstChunkSize;
        $operationId = \Illuminate\Support\Str::uuid()->toString();

        $gift = Gift::query()->select(['id', 'name', 'e_name', 'type', 'price', 'vip_level', 'is_play', 'img', 'show_img', 'show_img2'])
            ->where('id', $giftId)
            ->where('enable', 1)
            ->first();
        if (!$gift)
            throw new InvalidArgumentException(__('api_responses.giftNotFound'));

        $giftPrice = $gift->price;
        $receiversIds = array_map('intval', array_map('trim', explode(',', $data['toUid'])));
        $receiversCount = count($receiversIds);
        $numberOfGift = $number * $receiversCount;
        $totalPrice = $giftPrice * $numberOfGift;
        $totalPriceFull = $totalPrice * $firstChunkSize;

        // Acquire lock BEFORE validation to prevent race conditions
        $lock = $this->acquireUserLock($userId);
        try {
            $user->refresh();
            $userCoins = $user->di;
            $oldUserCoin = $userCoins;
            $amountBefore = $user->di;

            // Validate coins AFTER acquiring lock to ensure atomic check-and-deduct
            if ($userCoins < $totalPriceFull) {
                throw new InvalidArgumentException(__('api_responses.insufficient') . " (Required: {$totalPriceFull}, Available: {$userCoins})");
            }

            if (isset($ownerId)) {
                $room = Room::withoutAppends()
                    ->with('microphones')
                    ->where('uid', $ownerId)
                    ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                    ->first();
            } else {
                $room = Room::withoutAppends()
                    ->with('microphones')
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
            
            // FIX 2: Use actual receivers from DB instead of overwriting count
            // This prevents budget calculation from being based on potentially fewer users
            $receiversIds = $receivedUsers->pluck('id')->all();
            $receiversCount = count($receiversIds);
            
            $isToRoom = $receiversCount > 1;
            $responseData = $this->getResponseData2($gift, $room, $user, $receiversIds, $this->getReceiverName($isToRoom, $receiverName));

            $index = $count;
            $total_user_win = 0;
            $max_single_win = 0;
            $total_count_win = 0;
            $unitPrice = $giftPrice * $number;
            $fairService = app(\App\Services\FairLuck\V7\FairLuckServiceV7::class);
            $throwNumber = 0;

            //Fixed: per-receiver room owner fee (was using total for all receivers before)
            $coinsForOwnerPerReceiver = $unitPrice * $receiverFeeRate;
            $coinsForOwnerTotal = $coinsForOwnerPerReceiver * $receiversCount;

            $senderBalanceBefore = $user->di;
            $totalWalletsBefore = null;
            $totalWalletsAfter = null;

            $totalPriceFull = $giftPrice * $number * $receiversCount;

            // Track balance before any cashback wins for accurate logging
            $balanceBeforeAnyCashback = $user->di;

            while ($user->di >= $totalPriceFull && $index > 0) {

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
                    $netBetAmount = $unitPrice;

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
                        continue;
                    }

                    // Log deduction ONLY after successful processBet
                    UserCoinLogHelper::logByType(
                        $user->id,
                        -abs($unitPrice),
                        $senderBalanceBeforeHit,
                        UserCoinLogType::LUCKY_GIFT,
                        $gift?->name,
                    );

                    $iterationWin = 0;
                    $isWinner = false;
                    $multiplier = 0;
                    $message = null;

                    // FIX: Deduct gift cost FIRST, before adding cashback
                    $user->di -= $unitPrice;

                    if ($result) {
                        $isWinner = (bool) ($result->isWinner ?? false);
                        $multiplier = (float) ($result->multiplier ?? 0);
                        $iterationWin = (float) ($result->profitAmount ?? 0);

                        if ($isWinner && $iterationWin > 0) {
                            // Now add cashback AFTER deduction
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

                    $total_cashback_percentage += $multiplier;
                }

                $index--;
            }

            if ($total_user_win > 0) {
                // FIX: Track balance BEFORE cashback was added (before the loop started)
                // At this point $user->di already contains the cashback, so we need to calculate
                // what the balance was before any cashback was added during the loop
                $balanceBeforeCashbackLog = $balanceBeforeAnyCashback - ($throwNumber * $unitPrice);

                UserCoinLogHelper::logByType(
                    $userId,
                    $total_user_win,
                    $balanceBeforeCashbackLog,
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

            // ========================================================================
            // DEFERRED POST-PROCESSING (moved to async job)
            // ========================================================================
            // All post-processing is now dispatched to a queue job to prevent
            // worker blocking. The synchronous part is now complete.
            
            // Prepare response with current state
            $totalDiamond = $totalPrice * $count;
            $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_diamond_send, $totalDiamond, $user->sub_sender_level);
            $coinsForReceiverBase = $number * ($giftPrice * $hostPercentage);
            $price = $coinsForReceiverBase * $receiversCount;
            $coinsForReceiver = $coinsForReceiverBase * $count;
            $number = $number * $count;
            $roomSessionToAdd = $coinsForOwnerTotal * $count;

            // Use the current in-memory balance (already updated by the loop)
            // DO NOT refresh() here as it would discard in-memory changes
            $responseData['session'] = $room->session_string;
            $responseData['user_coins'] = $user->di;
            $responseData['gift_num'] = $receiversCount * $number;
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

            $responseData['total_pk'] = $coinsForReceiver;

            // CRITICAL: Save user balance changes to DB BEFORE returning response
            // This ensures subsequent requests see the updated balance
            // Using atomic DB update to avoid Eloquent observer issues

            $initialDi = $user->getOriginal('di');
            $finalDi = $user->di;
            $totalChange = $finalDi - $initialDi;

            Log::channel('lucky_gift')->info('💾 Applying balance change', [
                'user_id' => $userId,
                'initial_di' => $initialDi,
                'final_di' => $finalDi,
                'change' => $totalChange,
                'total_user_win' => $total_user_win,
                'throwNumber' => $throwNumber,
            ]);

            // Atomic update with optimistic locking to prevent race conditions
            $updated = \DB::table('users')
                ->where('id', $userId)
                ->where('di', $initialDi)  // Ensure no concurrent modification
                ->update([
                    'di' => $finalDi,
                    'updated_at' => now(),
                ]);

            if (!$updated) {
                $currentDi = \DB::table('users')->where('id', $userId)->value('di');
                Log::channel('lucky_gift')->error('❌ Balance update failed - concurrent modification detected', [
                    'user_id' => $userId,
                    'expected_initial_di' => $initialDi,
                    'current_di' => $currentDi,
                    'attempted_final_di' => $finalDi,
                ]);
                throw new \Exception('Balance was modified by another request. Please try again.');
            }

            // Verify the update
            $verifiedDi = \DB::table('users')->where('id', $userId)->value('di');
            Log::channel('lucky_gift')->info('✅ Balance updated successfully', [
                'user_id' => $userId,
                'new_balance' => $verifiedDi,
                'change_applied' => $totalChange,
                'verified' => ($verifiedDi == $finalDi),
            ]);

            // Sync the Eloquent model to reflect the saved state
            // This prevents isDirty() from thinking the model still needs saving
            $user->syncOriginal();

            // Dispatch post-processing job ASYNCHRONOUSLY
            // This prevents worker blocking and reduces response time to < 10s
            \App\Jobs\ProcessLuckyGiftPostJob::dispatch([
                'user_id' => $userId,
                'room_id' => $roomId,
                'gift_id' => $giftId,
                'receivers_ids' => $receiversIds,
                'count' => $count,
                'total_price' => $totalPrice,
                'total_user_win' => $total_user_win,
                'total_count_win' => $total_count_win,
                'coins_for_receiver' => $coinsForReceiver,
                'total_diamond' => $totalDiamond,
                'room_session' => $roomSessionToAdd,
                'sender_level' => $senderLevel,
                'charizma_status' => $room->charizma_status,
                'last_pk' => $room->lastPk ?? false,
                'room_type' => $room->type,
                'host_percentage' => $hostPercentage,
                'room_boom_enabled' => true,
                'data' => $data,
                'number' => $number,
                'price' => $price,
                'user_coins_before' => $oldUserCoin,
                'user_coins_after' => $user->di,
                'owner_id' => $ownerId,
            ])->onQueue('gifts');

            return $responseData;
        } finally {
            $lock->release();
        }
    }



    public function sendLuckyGiftV1(array $data, User $user, UpdateUserWhenSendGift $updateUserWhenSendGift)
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
                ->with('microphones')
                ->where('uid', $ownerId)
                ->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,total_diamond,level,type,level_id,microphone,charizma_status')
                ->first();
        } else {
            $room = Room::withoutAppends()
                ->with('microphones')
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

        // Track balance before any cashback wins for accurate logging (V1)
        $balanceBeforeAnyCashbackV1 = $user->di;
        $totalGiftsSent = 0;

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

            // FIX: Deduct gift cost FIRST, before calculating/adding cashback
            $user->di -= $totalPrice;

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
                            // Now add cashback AFTER deduction
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

            $totalGiftsSent++;
            $index--;
            $total_cashback_percentage += $iterationMaxCashback;
        }



        if ($total_user_win > 0) {
            // FIX: Track balance BEFORE cashback was added (before the loop started)
            // At this point $user->di already contains the cashback, so we need to calculate
            // what the balance was before any cashback was added during the loop
            $balanceBeforeCashbackLogV1 = $balanceBeforeAnyCashbackV1 - ($totalGiftsSent * $totalPrice);

            UserCoinLogHelper::logByType(
                $userId,
                $total_user_win,
                $balanceBeforeCashbackLogV1,
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

        // ========================================================================
        // DEFERRED POST-PROCESSING (moved to async job)
        // ========================================================================
        // All post-processing is now dispatched to a queue job to prevent
        // worker blocking. The synchronous part is now complete.

        // Prepare response with current state
        $totalDiamond = $totalPrice * $count;
        $senderLevel = $updateUserWhenSendGift->getSenderLevel($user->total_sender_diamonds, $totalDiamond, $user->sub_sender_level);
        
        $coinsForReceiver = $coinsForReceiver * $count;
        $number = $number * $count;

        // Use the current in-memory balance (already updated by the loop)
        // DO NOT refresh() here as it would discard in-memory changes
        $newUserCoin = $user->di;

        // add session to response
        $responseData['session'] = $room->session_string;
        $responseData['user_coins'] = $newUserCoin;
        $responseData['gift_num'] = $receiversCount * $number;
        $responseData['total_price'] = $totalPrice;
        $responseData['cashback_percentage'] = $total_cashback_percentage;
        $responseData['total_user_win'] = $total_user_win;
        $responseData['gift_name'] = app()->getLocale() === 'ar' ? $gift->name ?? $gift->e_name : $gift->e_name ?? $gift->name;
        $responseData['summary'] = [
            'total_win' => $total_user_win,
            'max_single_win' => $max_single_win,
            'total_win_count' => $total_count_win,
        ];

        // CRITICAL: Save user balance changes to DB BEFORE returning response
        // This ensures subsequent requests see the updated balance
        // Using atomic DB update to avoid Eloquent observer issues

        $initialDiV1 = $user->getOriginal('di');
        $finalDiV1 = $user->di;
        $totalChangeV1 = $finalDiV1 - $initialDiV1;

        Log::channel('lucky_gift')->info('💾 [V1] Applying balance change', [
            'user_id' => $userId,
            'initial_di' => $initialDiV1,
            'final_di' => $finalDiV1,
            'change' => $totalChangeV1,
            'total_user_win' => $total_user_win,
        ]);

        // Atomic update with optimistic locking
        $updatedV1 = \DB::table('users')
            ->where('id', $userId)
            ->where('di', $initialDiV1)
            ->update([
                'di' => $finalDiV1,
                'updated_at' => now(),
            ]);

        if (!$updatedV1) {
            $currentDiV1 = \DB::table('users')->where('id', $userId)->value('di');
            Log::channel('lucky_gift')->error('❌ [V1] Balance update failed - concurrent modification', [
                'user_id' => $userId,
                'expected_initial_di' => $initialDiV1,
                'current_di' => $currentDiV1,
            ]);
            throw new \Exception('Balance was modified by another request. Please try again.');
        }

        Log::channel('lucky_gift')->info('✅ [V1] Balance updated successfully', [
            'user_id' => $userId,
            'new_balance' => $finalDiV1,
            'change_applied' => $totalChangeV1,
        ]);

        // Sync the Eloquent model to reflect the saved state
        $user->syncOriginal();

        // Dispatch post-processing job ASYNCHRONOUSLY
        // This prevents worker blocking and reduces response time to < 10s
        \App\Jobs\ProcessLuckyGiftPostJob::dispatch([
            'user_id' => $userId,
            'room_id' => $roomId,
            'gift_id' => $giftId,
            'receivers_ids' => $receiversIds,
            'count' => $count,
            'total_price' => $totalPrice,
            'total_user_win' => $total_user_win,
            'total_count_win' => $total_count_win,
            'coins_for_receiver' => $coinsForReceiver,
            'total_diamond' => $totalDiamond,
            'room_session' => $coinsForOwner * $count,
            'sender_level' => $senderLevel,
            'charizma_status' => $room->charizma_status,
            'last_pk' => $room->lastPk ?? false,
            'room_type' => $room->type,
            'host_percentage' => $hostPercentage,
            'room_boom_enabled' => true,
            'data' => $data,
            'number' => $number,
            'price' => $price,
            'user_coins_before' => $oldUserCoin,
            'user_coins_after' => $newUserCoin,
            'owner_id' => $ownerId,
            'app_wallet_diff' => $appWallet->coins - $firstAppWalletCoins,
            'owner_wallet_diff' => $ownerWallet->coins - $firstOwnerWalletCoins,
        ])->onQueue('gifts');

        return $responseData;
        } finally {
            $lock->release();
        }
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
        if (!$room->relationLoaded('microphones')) {
            $room->load('microphones');
        }

        $microphones = $room->microphones;

        $positions = [];
        $missingReceivers = [];

        foreach ($receiversIds as $receiverId) {
            $mic = $microphones->firstWhere('user_id', $receiverId);
            if ($mic) {
                $positions[] = $mic->position;
            } else {
                $positions[] = -1;
                $missingReceivers[] = $receiverId;
            }
        }

        if (!empty($missingReceivers)) {
            \Illuminate\Support\Facades\Log::warning('Lucky gift: receivers without room_microphones records', [
                'room_id' => $room->id,
                'missing_receiver_ids' => $missingReceivers,
                'total_receivers' => count($receiversIds),
                'with_records' => count($receiversIds) - count($missingReceivers),
                'all_microphones' => $microphones->map(fn($m) => [
                    'position' => $m->position,
                    'user_id' => $m->user_id,
                    'user_id_type' => gettype($m->user_id),
                ])->all(),
                'requested_receivers_types' => array_map('gettype', $receiversIds),
            ]);
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
