<?php

namespace Utd\Gifts\Services;

use App\Helpers\Common;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Utd\Gifts\DTOs\SendGiftDTO;
use Utd\Gifts\Entities\Gift;
use Utd\Gifts\Entities\GiftLog;
use Utd\Gifts\Events\GiftSending;
use Utd\Gifts\Events\GiftSent;
use Utd\Gifts\Exceptions\GiftNotFoundException;
use Utd\Gifts\Exceptions\InsufficientBalanceException;
use Utd\Gifts\Exceptions\VipLevelRequiredException;
use Utd\Gifts\Repositories\GiftRepository;

class GiftSenderService
{
    public function __construct(
        private GiftRepository $giftRepository,
        private BalanceService $balanceService,
    ) {}

    /**
     * Method Main - إرسال الهدية
     */
    public function send(SendGiftDTO $dto): Collection
    {
        // 1. Get gift
        $gift = $this->giftRepository->findById($dto->giftId);
        if (! $gift) {
            throw new GiftNotFoundException("Gift not found: {$dto->giftId}");
        }

        // 2. Get sender
        $sender = $this->getSender($dto->senderId);

        // 3. Calculate total
        $totalPrice = $this->calculateTotalPrice(
            $gift,
            $dto->quantity,
            $dto->getTotalReceivers()
        );

        // 4. Validate
        $this->validate($dto, $gift, $sender, $totalPrice);

        // 5. Fire "Sending" event (قبل الإرسال)
        $event = new GiftSending($dto, $gift, $sender, $totalPrice);
        if (event($event) === false) {
            throw new Exception('Gift sending was cancelled');
        }

        // 6. Execute in transaction
        return DB::transaction(function () use ($dto, $gift, $sender, $totalPrice) {
            // 6.1 Deduct from sender (Synchronous - متزامن)
            $this->balanceService->deductFromSender($sender, $totalPrice);

            // 6.2 Create gift logs
            $logs = $this->createGiftLogs($dto, $gift, $totalPrice);

            // 6.3 Fire "Sent" event (للـ side effects)
            event(new GiftSent($dto, $gift, $sender, $logs, $totalPrice));

            return $logs;
        });
    }

    public function canSend(SendGiftDTO $dto): bool
    {
        try {
            $gift = $this->giftRepository->findById($dto->giftId);
            $sender = $this->getSender($dto->senderId);
            $totalPrice = $this->calculateTotalPrice($gift, $dto->quantity, $dto->getTotalReceivers());

            $this->validate($dto, $gift, $sender, $totalPrice);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function calculateTotalPrice(Gift $gift, int $quantity, int $receiversCount): int
    {
        return $gift->price * $quantity * $receiversCount;
    }

    public function checkVipRequirement(Gift $gift, int $userId): bool
    {
        if ($gift->vip_level <= 0) {
            return true;
        }

        $user = $this->getSender($userId);
        if (! $user) {
            return false;
        }

        $userVipLevel = $this->getUserVipLevel($user);

        return $userVipLevel >= $gift->vip_level;
    }

    /**
     * Validation
     */
    private function validate(SendGiftDTO $dto, Gift $gift, User $sender, int $totalPrice): void
    {
        // Check balance
        if (! $this->balanceService->hasSufficientBalance($sender, $totalPrice)) {
            throw new InsufficientBalanceException(
                "Insufficient balance. Required: {$totalPrice}, Available: {$sender->di}"
            );
        }

        // Check VIP level
        if (! $this->checkVipRequirement($gift, $sender->id)) {
            throw new VipLevelRequiredException(
                "VIP level {$gift->vip_level} required to send this gift"
            );
        }
    }

    /**
     * Create gift logs
     */
    private function createGiftLogs(SendGiftDTO $dto, Gift $gift, int $totalPrice): Collection
    {
        $pricePerReceiver = $totalPrice / $dto->getTotalReceivers();
        $batchUuid = (string) Str::uuid();
        $now = now();
        $logs = [];

        foreach ($dto->receiverIds as $receiverId) {
            $logs[] = [
                'batch_uuid' => $batchUuid,
                'gift_id' => $gift->id,
                'sender_id' => $dto->senderId,
                'receiver_id' => $receiverId,
                'gift_num' => $dto->quantity,
                'gift_name' => $gift->name,
                'gift_price' => $pricePerReceiver,
                'source_type' => $dto->sourceType,
                'source_id' => $dto->sourceId,
                'room_id' => $dto->getRoomId(),
                'moment_id' => $dto->getMomentId(),
                'pk_id' => $dto->getPkId(),
                'cp_id' => $dto->getCpId(),
                'is_play' => $gift->is_play ? 2 : 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        GiftLog::insert($logs);

        return GiftLog::where('batch_uuid', $batchUuid)->get();
    }

    private function getSender(int $userId)
    {
        return User::find($userId);
    }

    private function getUserVipLevel($user): int
    {
        $vipData = Common::ovip_center($user);

        return $vipData?->level ?? 0;
    }
}
