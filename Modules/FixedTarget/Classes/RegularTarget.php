<?php

namespace Modules\FixedTarget\Classes;

use App\Helpers\Common;
use App\Models\Target;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Modules\FixedTarget\Interfaces\TargetInterface;

class RegularTarget implements TargetInterface
{

    public function getTarget(int $diamond): Model|null
    { 
        return Target::query()->where('diamonds', '<=', $diamond)->orderBy('diamonds', 'desc')->first();
    }

    public function calculateUsdFromTarget(Model $target, float $hours, int $days , array $extra): float
    {
        $targetReel =  explode(',', $target->reel);
        $targetMoment = explode(',', $target->moment);
        $extras = $extra;
        $per = 0.50;
        if ($target->hours <= $hours) {
            $per += (settings()->get('hours') ?? 0)/100;
        }
        if ($target->days <= $days) {
            $per +=  (settings()->get('days')?? 0) /100;
        }


        if((@$targetMoment[0] ?? 0) <= $extras['moment']['upload'] && (@$targetMoment[1]??0) <= $extras['moment']['likes'] && (@$targetMoment[2] ?? 0) <= $extras['moment']['comments'] )
        {

            $per += (settings()->get('moments')??0) /100;
        }

        if((@$targetReel[0] ?? 0) <= $extras['reel']['upload'] && (@$targetReel[1] ?? 0) <= $extras['reel']['likes'] && (@$targetReel[2] ?? 0 )<= $extras['reel']['comments'] )
        {
            $per += (settings()->get('reels') ?? 0) /100;
        }
//        if (Common::getConf('all_target_or_nothing') == 'true') {
//            if ($per < 1) {
//                $per = 0;
//            }
//        }

          
          $usd = Common::getTargetUsd($target->diamonds,$target->usd);
          
          return $usd * $per;
    }
}
