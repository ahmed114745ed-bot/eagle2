<?php

namespace Utd\Pk\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Utd\Pk\Entities\PkEvent;
use Utd\Pk\Entities\PkReward;

class PkEventCommand extends Command
{
    protected $signature = 'pk-event-update';

    protected $description = 'Auto-create new PK event when current one ends';

    public function handle()
    {
        $pkEvent = PkEvent::dayEnd()->first();

        if (! $pkEvent) {
            return '';
        }

        $lastEndDate = PkEvent::max('end_date');
        $newStartDate = Carbon::parse($lastEndDate)->toDateString();
        $newEndDate = Carbon::parse($lastEndDate)->addWeek()->toDateString();

        $newPkEvent = new PkEvent([
            'admin_id' => $pkEvent->admin_id,
            'start_date' => $newStartDate,
            'end_date' => $newEndDate,
            'editor_id' => $pkEvent->editor_id,
        ]);
        $newPkEvent->save();

        $this->repeatRewards($pkEvent, $newPkEvent->id);
    }

    public function repeatRewards(PkEvent $pkEvent, int $pkEventNewId)
    {
        $columns = [
            'pk_event_id',
            'type',
            'level',
            'target',
            'pk_type',
            'expire',
            'created_at',
            'updated_at',
        ];
        $previousRewards = $pkEvent->rewards()->get($columns)->toArray();

        $reward = new PkReward;
        $appends = $reward->getAppends();
        foreach ($previousRewards as &$previousReward) {
            $previousReward['pk_event_id'] = $pkEventNewId;
            $previousReward['created_at'] = now();
            $previousReward['updated_at'] = now();

            foreach ($appends as $append) {
                unset($previousReward[$append]);
            }
        }

        PkReward::query()->insert($previousRewards);
    }
}
