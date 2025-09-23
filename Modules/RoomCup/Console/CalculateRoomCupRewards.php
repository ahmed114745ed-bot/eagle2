<?php

namespace Modules\RoomCup\Console;

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
    protected $description = 'حساب الكوينزات اليومية وتوزيع المكاسب للمالك والمديرين إذا تحقق التارجت';

    private function getSettings(): array
    {
        $path = storage_path('app/roomcup_settings.json');
        if (!file_exists($path)) {
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

        TotalRoomGift::whereDate('updated_at', $today)
            ->chunk(100, function ($gifts) {
                foreach ($gifts as $gift) {
                    $this->processGift($gift);
                }
            });

        return EnumCommand::SUCCESS;
    }

    private function processGift(TotalRoomGift $gift): void
    {
        $room = Room::find($gift->room_id);

        if (!$room) {
            $this->warn("⛔ روم غير موجود (ID: {$gift->room_id})");
            return;
        }

        $adminsCount     = $room->admins()->count();
        $visitorsCount   = $gift->number_of_visitors ?? 0;
        $target          = $this->findTarget($gift->current_total, $visitorsCount, $adminsCount);

        if (!$target) {
            $this->line("⛔ لم يتحقق التارجت للروم #{$room->id}");
            return;
        }

        DB::transaction(function () use ($room, $gift, $target, $adminsCount) {
            $rewards = [];

            $rewards[] = $this->makeReward($room->id, $gift->id, $room->owner_id, 'owner', $target->owner_profit);

            if ($adminsCount > 0 && $target->admin_profit > 0) {
                $share = $target->admin_profit / $adminsCount;
                foreach ($room->admins as $admin) {
                    $rewards[] = $this->makeReward($room->id, $gift->id, $admin->id, 'admin', $share);
                }
            }

            RoomCupReward::insert($rewards);

            foreach ($rewards as $reward) {
                User::whereKey($reward['user_id'])
                    ->increment('di', $reward['amount']);
                        
                    RoomCupHelper::updateRoomCupWallet($reward['amount']);
            }
        });

        $this->info("✅ تم توزيع الأرباح وإضافة الرصيد لروم #{$room->id}");
    }

    private function findTarget(float $total, int $visitors, int $admins): ?RoomCupTarget
    {
        return RoomCupTarget::where('total', '<=', $total)
            ->where('number_of_visitors', '<=', $visitors)
            ->where('number_of_admins', '<=', $admins)
            ->orderByDesc('total')
            ->first();
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
