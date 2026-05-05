<?php

namespace App\Jobs;

use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Models\Room;
use App\Models\User;
use App\Services\Gifts\LuckyGiftService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Public\Http\Services\UpgradeRoomLevelServices;
use Modules\RoomBoom\Services\NewRoomBoomGiftService;
use App\Http\Services\RoomService;
use Carbon\Carbon;
use App\Helpers\CacheHelper;

/**
 * ProcessLuckyGiftPostJob
 *
 * Handles all post-processing operations for lucky gift sends:
 * - Cache updates
 * - User updates
 * - Charisma dispatch
 * - Room boom processing
 * - Level upgrades
 *
 * This job runs AFTER the main gift transaction completes,
 * allowing the HTTP response to return quickly (< 10 seconds)
 */
class ProcessLuckyGiftPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes max
    public $tries = 3;
    public $backoff = [60, 120, 300]; // Exponential backoff

    public function __construct(
        public array $payload
    ) {}

    public function handle()
    {
        // ══════════════════════════════════════════════════════════════
        // DEDUPLICATION: Prevent duplicate job execution
        // ══════════════════════════════════════════════════════════════
        $jobIdentifier = $this->getJobIdentifier();

        // Check if this job was already processed
        $alreadyProcessed = DB::table('processed_jobs')
            ->where('job_identifier', $jobIdentifier)
            ->exists();

        if ($alreadyProcessed) {
            Log::info('ProcessLuckyGiftPostJob skipped - already processed', [
                'job_identifier' => $jobIdentifier,
                'user_id' => $this->payload['user_id'] ?? null,
            ]);
            return; // Exit early - job already processed
        }

        try {
            $userId = $this->payload['user_id'] ?? null;
            $roomId = $this->payload['room_id'] ?? null;
            $receiversIds = $this->payload['receivers_ids'] ?? [];
            $giftId = $this->payload['gift_id'] ?? null;
            $coinsForReceiver = $this->payload['coins_for_receiver'] ?? 0;
            $totalPrice = $this->payload['total_price'] ?? 0;
            $hostPercentage = $this->payload['host_percentage'] ?? 0;
            $count = $this->payload['count'] ?? 1;
            $totalDiamond = $this->payload['total_diamond'] ?? 0;
            $charizmStatus = $this->payload['charizma_status'] ?? false;
            $lastPk = $this->payload['last_pk'] ?? false;
            $roomType = $this->payload['room_type'] ?? null;
            $ownerId = $this->payload['owner_id'] ?? null;
            $appWalletDiff = $this->payload['app_wallet_diff'] ?? 0;
            $ownerWalletDiff = $this->payload['owner_wallet_diff'] ?? 0;
            $senderLevel = $this->payload['sender_level'] ?? null;
            $roomSession = $this->payload['room_session'] ?? 0;

            if ($roomId && $roomSession > 0) {
                try {
                    Room::where('id', $roomId)->increment('session', $roomSession);
                } catch (\Throwable $e) {
                    Log::warning('Failed to update room session', [
                        'room_id' => $roomId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // DISABLED: user->save() in sendLuckyGiftV2 (line 357) already persists balance changes
            // Calling updateUserCoinsAndDiamond here causes DUPLICATE DEDUCTION because:
            // 1. sendLuckyGiftV2 deducts from user->di and saves to DB
            // 2. updateUserCoins calculates difference and applies it again to DB
            // Result: balance deducted TWICE (e.g., 1000 becomes 2000 deduction)

            // if ($userId && $totalDiamond > 0) {
            //     try {
            //         $userCoinsBefore = $this->payload['user_coins_before'] ?? $this->payload['user_coin_before'] ?? 0;
            //         $userCoinsAfter = $this->payload['user_coins_after'] ?? $this->payload['user_coin_after'] ?? 0;
            //         $this->updateUserCoinsAndDiamond($userId, $userCoinsBefore, $userCoinsAfter, $totalDiamond, $senderLevel);
            //     } catch (\Throwable $e) {
            //         Log::warning('Failed to update user coins in ProcessLuckyGiftPostJob', [
            //             'user_id' => $userId,
            //             'error' => $e->getMessage(),
            //         ]);
            //     }
            // }

            // Instead, only update total_diamond_send and sender_level (without touching balance)
            if ($userId && $totalDiamond > 0) {
                try {
                    $updateData = [
                        'total_diamond_send' => DB::raw("total_diamond_send + {$totalDiamond}"),
                    ];

                    if ($senderLevel !== null) {
                        $updateData['sender_level'] = $senderLevel;
                    }

                    DB::table('users')
                        ->where('id', $userId)
                        ->update($updateData);

                    Log::channel('lucky_gift_receiver_issue')->error('UPDATED total_diamond_send + sender_level ONLY (no balance change)', [
                        'user_id' => $userId,
                        'total_diamond' => $totalDiamond,
                        'sender_level' => $senderLevel,
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to update total_diamond_send in ProcessLuckyGiftPostJob', [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($appWalletDiff !== 0 || $ownerWalletDiff !== 0) {
                try {
                    $this->updateCoreWallets($appWalletDiff, $ownerWalletDiff);
                } catch (\Throwable $e) {
                    Log::warning('Failed to update core wallets in ProcessLuckyGiftPostJob', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 1. Update cache
            if ($userId && $roomId && $giftId) {
                $userCoinsBeforeCache = $this->payload['user_coins_before'] ?? $this->payload['user_coin_before'] ?? 0;
                $userCoinsAfterCache = $this->payload['user_coins_after'] ?? $this->payload['user_coin_after'] ?? 0;
                $this->updateCache(
                    $userId,
                    $roomId,
                    $receiversIds,
                    $giftId,
                    $this->payload['data'] ?? [],
                    $this->payload['number'] ?? 0,
                    $totalPrice,
                    $coinsForReceiver,
                    $userCoinsBeforeCache,
                    $userCoinsAfterCache,
                    $this->payload['total_user_win'] ?? 0,
                    $this->payload['total_count_win'] ?? 0
                );
            }

            // 2. Update receiver users
            if (!empty($receiversIds) && $coinsForReceiver > 0) {
                $this->updateReceiverUsers($coinsForReceiver, $receiversIds);
            }

            // 3. Dispatch charisma if needed
            if ($charizmStatus && $coinsForReceiver > 1 && $roomId && $userId) {
                dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds);
            } elseif ($lastPk && $coinsForReceiver > 1 && $roomId && $userId) {
                dispatchRoomsRedis($roomId, $userId, $coinsForReceiver, $receiversIds, "pk");
            }

            // 4. Process room boom
            if ($roomId && $totalPrice > 0) {
                $this->processRoomBoom($roomId, $totalPrice, $hostPercentage, $count, $userId);
            }

            // 5. Process room level upgrade
            if ($roomType === 'audio' && $roomId && $totalPrice > 0) {
                $this->upgradeRoomLevel($roomId, $totalPrice, $count);
            }

            // ══════════════════════════════════════════════════════════════
            // Mark job as processed (AFTER successful completion)
            // ══════════════════════════════════════════════════════════════
            DB::table('processed_jobs')->insertOrIgnore([
                'job_identifier' => $jobIdentifier,
                'job_type' => self::class,
                'processed_at' => now(),
            ]);

//            Log::info('ProcessLuckyGiftPostJob completed successfully', [
//                'user_id' => $userId,
//                'room_id' => $roomId,
//                'receivers_count' => count($receiversIds),
//            ]);
        } catch (\Throwable $e) {
            // Don't mark as processed if failed - allow retry
            Log::error('ProcessLuckyGiftPostJob failed', [
                'error' => $e->getMessage(),
                'payload' => $this->payload,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Update user coins and diamonds
     */
    private function updateUserCoinsAndDiamond(int $userId, float $userCoinsBefore, float $userCoinsAfter, int $totalDiamond, ?int $senderLevel = null): void
    {
        try {
            $luckyGiftService = app(LuckyGiftService::class);
            $luckyGiftService->updateUserCoins($userId, $userCoinsAfter, $userCoinsBefore, $totalDiamond, senderLevel: $senderLevel);
        } catch (\Throwable $e) {
            Log::warning('Failed to update user coins and diamonds', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update core wallets (for sendLuckyGift2V3)
     */
    private function updateCoreWallets(float $appWalletDiff, float $ownerWalletDiff): void
    {
        try {
            $luckyGiftService = app(LuckyGiftService::class);
            $luckyGiftService->updateCoreWallet($appWalletDiff, $ownerWalletDiff);
        } catch (\Throwable $e) {
            Log::warning('Failed to update core wallets', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update cache with gift transaction data
     */
    private function updateCache(
        int $userId,
        int $roomId,
        array $receiversIds,
        int $giftId,
        array $data,
        int $number,
        float $totalPrice,
        float $coinsForReceiver,
        float $userCoinsBefore,
        float $userCoinsAfter,
        int $totalUserWin,
        int $totalCountWin
    ): void {
        try {
            $luckyGiftService = app(LuckyGiftService::class);
            $luckyGiftService->updateCache(
                $userId,
                $roomId,
                $receiversIds,
                $giftId,
                $data,
                $number,
                $totalPrice,
                $coinsForReceiver,
                $userCoinsBefore,
                $userCoinsAfter,
                $totalUserWin,
                $totalCountWin
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to update cache in ProcessLuckyGiftPostJob', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update receiver users with coins
     */
    private function updateReceiverUsers(float $coinsForReceiver, array $receiversIds): void
    {
        try {
            $updateUserWhenSendGift = app(UpdateUserWhenSendGift::class);
            $updateUserWhenSendGift->updateUsers($coinsForReceiver, $receiversIds);
        } catch (\Throwable $e) {
            Log::warning('Failed to update receiver users in ProcessLuckyGiftPostJob', [
                'error' => $e->getMessage(),
                'receivers_count' => count($receiversIds),
            ]);
        }
    }

    /**
     * Process room boom gift
     */
    private function processRoomBoom(
        int $roomId,
        float $totalPrice,
        float $hostPercentage,
        int $count,
        int $userId
    ): void {
        try {
            $settings = CacheHelper::cacheSettings();
            if (gettype($settings) !== 'array') {
                $settings = $settings->pluck('value', 'key')->toArray();
            }

            $roomBoomSettings = $settings['room_boom'] ?? 1;
            $totalHostDiamond = (int)($totalPrice * $hostPercentage);

            if ($roomBoomSettings) {
                $room = Room::find($roomId);
                if ($room) {
                    (new NewRoomBoomGiftService())->sendGift($room, $totalHostDiamond, $userId);
                }
            } else {
                $tz = getTimezone();
                $todayStart = Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');
                $totalRoomGift = (new RoomService())->getOrCreateTotalRoomGift($roomId, $todayStart);
                $totalRoomGift->increment('current_total', $totalHostDiamond);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to process room boom in ProcessLuckyGiftPostJob', [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
            ]);
        }
    }

    /**
     * Upgrade room level
     */
    private function upgradeRoomLevel(int $roomId, float $totalPrice, int $count): void
    {
        try {
            $room = Room::find($roomId);
            if ($room) {
                $serviceLevel = new UpgradeRoomLevelServices();
                $serviceLevel->sendGift($room, $totalPrice * $count);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to upgrade room level in ProcessLuckyGiftPostJob', [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
            ]);
        }
    }

    /**
     * Generate unique identifier for this job
     *
     * Creates a unique hash based on critical payload fields to identify
     * duplicate job executions. Uses user_id, receivers, and coins to ensure
     * the same operation isn't processed twice.
     *
     * @return string MD5 hash of critical payload fields
     */
    private function getJobIdentifier(): string
    {
        // Create identifier from critical fields that make this job unique
        $criticalData = [
            'user_id' => $this->payload['user_id'] ?? null,
            'receivers_ids' => $this->payload['receivers_ids'] ?? [],
            'coins_for_receiver' => $this->payload['coins_for_receiver'] ?? 0,
            'room_id' => $this->payload['room_id'] ?? null,
            'gift_id' => $this->payload['gift_id'] ?? null,
            'count' => $this->payload['count'] ?? 1,
            // Add timestamp to make each request unique (even if same params)
            // This prevents deduplication across different actual requests
            'user_coins_before' => $this->payload['user_coins_before'] ?? 0,
            'user_coins_after' => $this->payload['user_coins_after'] ?? 0,
        ];

        return md5(json_encode($criticalData));
    }

    public function failed(\Throwable $exception)
    {
        Log::error('ProcessLuckyGiftPostJob permanently failed', [
            'error' => $exception->getMessage(),
            'payload' => $this->payload,
        ]);
    }
}

