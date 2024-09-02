<?php

namespace Modules\Events\Console;

use Carbon\Carbon;
use App\Models\OVip;
use App\Models\Ware;
use App\Models\GiftLog;
use App\Helpers\UserCommon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Events\Entities\PkEvent;
use Modules\Events\Entities\PkWinner;
use Modules\Achievement\Entities\UserAchievementLevel;

class PKEventWinnerCommand extends Command
{
    protected $signature = 'pk-event-winner';

    protected $description = 'Command description';

    public function handle()
    {
        $pkEvent = $this->getCurrentPkEvent();
        if (!$pkEvent) {
            return '';
        }

        $this->processEventParticipants($pkEvent, 'sender', 'pk-king');
        $this->processEventParticipants($pkEvent, 'receiver', 'pk-star');
        $this->processEventParticipants($pkEvent, 'roomowner', 'pk-room');
    }

    protected function getCurrentPkEvent()
    {
        return PkEvent::endToday()->with('rewards')
            ->first();
    }

    protected function processEventParticipants(PkEvent $pkEvent, $participantType, $pkType)
    {
        $participants = $this->getEventParticipants($pkEvent, $participantType);
        foreach ($participants as $index => $participant) {
            if ($this->isAlreadyWinner($pkEvent->id, $participant->{$participantType . '_id'})) {
                continue;
            }
            $winner = $this->createWinner($pkEvent->id, $participant->{$participantType . '_id'}, $index + 1, $pkType);
            $this->assignRewards($winner, $pkEvent->rewards->where('level', $index + 1), $participant->{$participantType});
        }
    }

    protected function getEventParticipants($pkEvent, $participantType)
    {
        $column = $participantType === 'roomowner' ? 'roomowner_id' : $participantType . '_id';
        $notZero = $participantType === 'roomowner' ? '!=' : '=';
        return GiftLog::whereBetween('created_at', [$pkEvent->start_date, $pkEvent->end_date])
            ->with([$participantType])
            ->where('pk', 1)
            ->select(DB::raw('SUM(giftPrice) AS total_gift_num'), $column)
            ->groupBy($column)
            ->orderByDesc('total_gift_num')
            ->take(3)
            ->get();
    }

    protected function isAlreadyWinner($pkEventId, $userId)
    {
        return PkWinner::where([
            'pk_event_id' => $pkEventId,
            'user_id' => $userId,
        ])->exists();
    }

    protected function createWinner($pkEventId, $userId, $level, $pkType)
    {
        return PkWinner::create([
            'pk_event_id' => $pkEventId,
            'user_id' => $userId,
            'level' => $level,
            'pk_type' => $pkType,
        ]);
    }

    protected function assignRewards($winner, $rewardIds, $user)
    {
        foreach ($rewardIds as $reward) {
            DB::table('reward_winner_pks')->insert([
                'pk_winner_id' => $winner->user_id,
                'pk_reward_id' => $reward->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            switch ($reward->type) {
                case "coins":
                    $user->di += $reward->target;
                    $user->save();
                    break;
                case "vip":
                    $vip = OVip::find($reward->target);
                    UserCommon::addVipToUser($user, $vip, $reward->expire);
                    break;
                case "ware":
                    $ware = Ware::find($reward->target);
                    UserCommon::addWareToUser($user, $ware, $reward->expire);
                    break;
                case "achievement":
                    $dateTimestamp = Carbon::parse($reward->expire)->format("Y-m-d H:i:s");
                    $attributes = [
                        'user_id'       => $user->id,
                        'custom_image' => $reward->target,
                        'end_at' => $dateTimestamp,
                    ];
                    UserAchievementLevel::create($attributes);
                    break;
            }
        }
    }
}
