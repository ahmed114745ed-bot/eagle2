<?php

namespace Modules\FixedTarget\Classes;

use App\Helpers\Common;
use App\Models\Target;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Modules\FixedTarget\Interfaces\TargetInterface;

class RegularTarget implements TargetInterface
{

    public function getTarget(int $diamond): ?Model
    {
        return Target::query()
            ->where('diamonds', '<=', $diamond)
            ->orderBy('diamonds', 'desc')
            ->first();
    }

    public function calculateUsdFromTarget(Model $target, float $hours, int $days, array $extra): float
    {
        $percentage = $this->calculatePercentageAchieved($target, $hours, $days, $extra);
        $usd = Common::getTargetUsd($target->diamonds, $target->usd);

        return $usd * $percentage;
    }

    public function calculatePercentageAchieved(Model $target, float $hours, int $days, array $extra): float
    {
        $percentage = Common::getDiamondsPercentage();

        $percentage += $this->checkHoursDays($target, $hours, $days);

        $percentage += $this->checkMoments($target, $extra);
        $percentage += $this->checkReels($target, $extra);

        return $percentage;
    }

    private function checkHoursDays(Model $target, float $hours, int $days): float
    {
        $perc = 0;
        if ($target->hours <= $hours) {
            $perc += ((int) Common::getSettingsValue('hours', 0)) / 100;
        }
        if ($target->days <= $days) {
            $perc += ((int) Common::getSettingsValue('days', 0)) / 100;
        }
        return $perc;
    }

    private function checkMoments(Model $target, array $extra): float
    {
        $perc = 0;
        $targetMoment = explode(',', $target->moment);

        if (($targetMoment[0] ?? 0) <= ($extra['moment']['upload'] ?? 0) &&
            ($targetMoment[1] ?? 0) <= ($extra['moment']['likes'] ?? 0) &&
            ($targetMoment[2] ?? 0) <= ($extra['moment']['comments'] ?? 0)) {
            $perc += ((int) Common::getSettingsValue('moments', 0)) / 100;
        }
        return $perc;
    }

    private function checkReels(Model $target, array $extra): float
    {
        $perc = 0;
        $targetReel = explode(',', $target->reel);

        if (($targetReel[0] ?? 0) <= ($extra['reel']['upload'] ?? 0) &&
            ($targetReel[1] ?? 0) <= ($extra['reel']['likes'] ?? 0) &&
            ($targetReel[2] ?? 0) <= ($extra['reel']['comments'] ?? 0)) {
            $perc += ((int) Common::getSettingsValue('reels', 0)) / 100;
        }
        return $perc;
    }


}
