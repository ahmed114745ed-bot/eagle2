<?php

namespace Modules\RoomCup\Console;

use App\Enums\UserCoinLogType;
use App\Helpers\Common;
use App\Helpers\UserCoinLogHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\RoomCup\Entities\RoomCupTarget;
use Modules\RoomBoom\Entities\TotalRoomGift;
use Modules\RoomCup\Entities\RoomCupReward;
use App\Models\Room;
use App\Models\User;
use Modules\RoomCup\Helpers\RoomCupHelper;
use Symfony\Component\Console\Command\Command as EnumCommand ;

class CalculateRoomCupRewards extends Command
{
    protected $signature = 'roomcup:calculate-rewards';
    protected $description = 'Calculate daily RoomCup rewards and distribute profits to the owner and admins if the target is achieved';

    private function getSettings(): array
    {
        $path = storage_path('app/roomcup_settings.json');
        if (!file_exists($path)) {
            logger()->warning("⚠️ Settings file not found: $path");
            return ['enabled' => false, 'interval_minutes' => 60];
        }

        return json_decode(file_get_contents($path), true);
    }

    public function handle(): int
    {
        $settings = $this->getSettings();

        if (empty($settings['enabled']) || !$settings['enabled']) {
            $this->warn("❌ Room Cup feature disabled in settings");
            logger()->warning("❌ Room Cup feature disabled in settings");
            return EnumCommand::SUCCESS;
        }

        $today = Carbon::today();
        $this->info("🚀 Starting calculation for: {$today->toDateString()}");

        TotalRoomGift::whereDate('updated_at', $today)
            ->orderBy('id')
            ->chunk(100, function ($gifts) {
                foreach ($gifts as $gift) {
                    $this->processGift($gift);
                }
            });

        $this->info("✅ Daily calculation finished");
        return EnumCommand::SUCCESS;
    }

    private function processGift(TotalRoomGift $gift): void
    {
        $this->line("📦 Processing RoomGift ID: {$gift->id} | Room: {$gift->room_id} | Total: {$gift->current_total}");

        $room = Room::find($gift->room_id);

        if (!$room) {
            $this->warn("⛔ Room not found (ID: {$gift->room_id})");
            logger()->error("⛔ Room not found (ID: {$gift->room_id})");
            return;
        }

        $adminsCount   = $room->admins()->count();
        $visitorsCount = $gift->number_of_visitors ?? 0;

        $this->line("👥 Admins: $adminsCount | Visitors: $visitorsCount | Total: {$gift->current_total}");

        $target = $this->findTarget($gift->current_total, $visitorsCount, $adminsCount);

        if (!$target) {
            $this->line("⛔ No target achieved for Room #{$room->id}");
            return;
        }

        DB::transaction(function () use ($room, $gift, $target, $adminsCount) {
            $rewards = [];

            $rewards[] = $this->makeReward($room->id, $gift->id, $room->uid, 'owner', $target->owner_profit);
            $this->line("💰 Room owner #{$room->uid} will get {$target->owner_profit}");

            if ($adminsCount > 0 && $target->admin_profit > 0) {
                $share = $target->admin_profit / $adminsCount;
                foreach ($room->admins as $admin) {
                    $rewards[] = $this->makeReward($room->id, $gift->id, $admin->id, 'admin', $share);
                    $this->line("👤 Admin {$admin->id} will get $share");
                }
            }

            RoomCupReward::insert($rewards);

            foreach ($rewards as $reward) {
                $amountBefore = Common::getCurrentBalance($reward['user_id']);
                $this->line("🪙 Adding {$reward['amount']} to user {$reward['user_id']} (balance before: {$amountBefore})");

                UserCoinLogHelper::logByType(
                    $reward['user_id'],
                    $reward['amount'],
                    $amountBefore,
                    UserCoinLogType::ROOM_CUP,
                );

                User::whereKey($reward['user_id'])
                    ->increment('di', $reward['amount']);

                RoomCupHelper::updateRoomCupWallet($reward['amount']);
            }
        });

        $this->info("✅ Rewards distributed for Room #{$room->id}");
    }

    private function findTarget(float $total, int $visitors, int $admins): ?RoomCupTarget
    {
        $target = RoomCupTarget::where('total', '<=', $total)
            ->where('number_of_visitors', '<=', $visitors)
            ->where('number_of_admins', '<=', $admins)
            ->orderByDesc('total')
            ->first();

        if (!$target) {
            logger()->info("📉 No matching target (Total: $total, Visitors: $visitors, Admins: $admins)");
        } else {
            logger()->info("🎯 Target selected: ID {$target->id}, Owner Profit {$target->owner_profit}, Admin Profit {$target->admin_profit}");
        }

        return $target;
    }

    private function makeReward(int $roomId, int $giftId, int $userId, string $type, float $amount): array
    {
        return [
            'room_id'            => $roomId,
            'total_room_gift_id' => $giftId,
            'user_id'            => $userId,
            'type'               => $type,
            'amount'             => $amount,
            'created_at'         => now(),
            'updated_at'         => now(),
        ];
    }
}