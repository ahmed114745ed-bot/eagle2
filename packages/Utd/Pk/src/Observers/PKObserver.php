<?php

namespace Utd\Pk\Observers;

use Utd\Pk\Entities\Pk;

class PKObserver
{
    public function creating(Pk $pK): void
    {
        $this->setTeams($pK);
    }

    public function updating(Pk $pK): void
    {
        $this->setTeams($pK);
    }

    private function setTeams(Pk $pK): void
    {
        $mics = $pK->mics;
        $m = is_string($mics) ? explode(',', $mics) : $mics;
        $mic_1 = $m[1] ?? 0;
        $mic_2 = $m[2] ?? 0;
        $mic_3 = $m[3] ?? 0;
        $mic_4 = $m[4] ?? 0;
        $mic_5 = $m[5] ?? 0;
        $mic_6 = $m[6] ?? 0;
        $mic_7 = $m[7] ?? 0;
        $mic_8 = $m[8] ?? 0;
        $team_1 = [$mic_1, $mic_2, $mic_5, $mic_6];
        $team_2 = [$mic_3, $mic_4, $mic_7, $mic_8];
        $pK->team_1 = implode(',', $team_1);
        $pK->team_2 = implode(',', $team_2);
    }
}
