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
use Symfony\Component\Console\Command\Command as EnumCommand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;




class CalculateRoomCupRewards extends Command
{
    protected $signature = 'roomcup:calculate-rewards';
    protected $description = 'Calculate RoomCup rewards and distribute profits to the owner and admins if the target is achieved';

    public function handle(): int
    {
        $settings = $this->getRoomCupSettings();
        $type     = $settings['type'] ?? 'daily';

        if (!$this->isEnabledRoomCup($settings)) {
            $this->warn("⛔ Room Cup not enabled");
            return EnumCommand::SUCCESS;
        }

        [$start, $end] = $this->getPeriodByType($type);
        Log::info('Period by type', [
            'type'  => $type,
            'start' => $start,
            'end'   => $end,
        ]);

        $this->logStart($start, $end);

        $this->processGiftsInPeriod($start, $end);

        $this->logEnd();

        return EnumCommand::SUCCESS;
    }
    private function isEnabledRoomCup(array $settings): bool
    {
        return $settings['enabled'] ?? false;
    }


    private function getRoomCupSettings(): array
    {
        $default = [
            'enabled'          => true,
            'interval_minutes' => 60,
            'type'             => 'daily',
            'time'             => '23:59',
        ];

        if (!Storage::disk('local')->exists('roomcup_settings.json')) {
            Storage::disk('local')->put('roomcup_settings.json', json_encode($default, JSON_PRETTY_PRINT));
        }

        return array_merge($default, json_decode(Storage::disk('local')->get('roomcup_settings.json'), true) ?? []);
    }

    private function getPeriodByType(string $type): array
    {
        return match ($type) {
            'daily'   => [
                Carbon::yesterday(getTimezone())->startOfDay(),
                Carbon::yesterday(getTimezone())->endOfDay(),
            ],
            'weekly'  => [
                Carbon::now(getTimezone())->subWeek()->startOfWeek(),
                Carbon::now(getTimezone())->subWeek()->endOfWeek(),
            ],
            'monthly' => [
                Carbon::now(getTimezone())->subMonth()->startOfMonth(),
                Carbon::now(getTimezone())->subMonth()->endOfMonth(),
            ],
            default   => [
                Carbon::yesterday(getTimezone())->startOfDay(),
                Carbon::yesterday(getTimezone())->endOfDay(),
            ],
        };
    }

    private function processGiftsInPeriod(Carbon $start, Carbon $end): void
    {
        TotalRoomGift::whereBetween('created_at', [$start, $end])
            ->orderBy('id')
            ->chunk(100, function ($gifts) {
                foreach ($gifts as $gift) {
                    $this->processGift($gift);
                }
            });
    }

    private function logStart(Carbon $start, Carbon $end): void
    {
        $this->info("🚀 Starting full calculation for gifts between {$start} and {$end}");
    }

    private function logEnd(): void
    {
        $this->info("✅ Calculation finished");
    }

    private function processGift(TotalRoomGift $gift): void
    {
        $this->line("📦 Processing RoomGift ID: {$gift->id} | Room: {$gift->room_id} | Total: {$gift->current_total}");

        $room = Room::find($gift->room_id);


        if (!$room) {
            $this->warn("⛔ Room not found (ID: {$gift->room_id})");
            return;
        }



        $adminsCount   = $room->admins_v2()->count();
        $visitorsCount = $gift->number_of_visitors ?? 0;

        $this->line("👥 Admins: $adminsCount | Visitors: $visitorsCount | Total: {$gift->current_total}");

        $target = $this->findTarget($gift->current_total, $visitorsCount, $adminsCount);
        if ($gift->room_id == 215) {
            Log::info('Gift Debug Data', [
                'target' => $target,
                'room'   => $room,
            ]);
           
            Log::info('count', [
                'visitor' => $visitorsCount,
                'admin'   => $adminsCount,
            ]);
        }
        if (!$target) {
            $this->line("⛔ No target achieved for Room #{$room->id}");
            return;
        }
        $room->max_admin = $target->number_of_admins;
        $room->save();
        DB::transaction(function () use ($room, $gift, $target, $adminsCount) {
            $rewards = [];
            $targetId = $target->id;

            // Owner reward
            if ($target->owner_profit > 0) {
                $rewards[] = $this->makeReward($room->id, $gift->id, $targetId, $room->uid, 'owner', $target->owner_profit);
                $this->line("💰 Room owner #{$room->uid} will get {$target->owner_profit}");
            }

            if ($adminsCount > 0 && $target->admin_profit > 0) {
                $share = $target->admin_profit / $adminsCount;
                foreach ($room->admins_v2() as $admin) {
                    $rewards[] = $this->makeReward($room->id, $gift->id, $targetId, $admin->id, 'admin', $share);
                    $this->line("👤 Admin {$admin->id} will get $share");
                }
            }

            foreach ($rewards as $reward) {
                $exists = RoomCupReward::where('room_id', $reward['room_id'])
                    ->where('total_room_gift_id', $reward['total_room_gift_id'])
                    ->where('user_id', $reward['user_id'])
                    ->where('type', $reward['type'])
                    ->exists();

                if ($exists) {
                    $this->line("⏭️ Skipping duplicate reward for user {$reward['user_id']} in room {$reward['room_id']} (gift {$reward['total_room_gift_id']})");
                    continue;
                }

                RoomCupReward::create($reward);

                // Apply reward
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
        return RoomCupTarget::where('total', '<=', $total)
            ->where('number_of_visitors', '<=', $visitors)
            ->orderByDesc('total')
            ->first();
    }

    private function makeReward(int $roomId, int $giftId, $targetId,  int $userId, string $type, float $amount): array
    {
        return [
            'room_id'            => $roomId,
            'total_room_gift_id' => $giftId,
            'target_id'          => $targetId,
            'user_id'            => $userId,
            'type'               => $type,
            'amount'             => $amount,
            'created_at'         => now(),
            'updated_at'         => now(),
        ];
    }
}
