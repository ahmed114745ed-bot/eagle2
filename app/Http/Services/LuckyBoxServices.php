<?php

namespace App\Http\Services;

use App\Helpers\Common;
use App\Models\Banner;

class LuckyBoxServices
{
    public function getCoins($fin,$unusedCoins,$notUsedNum)
    {
        $coins=0;
        if($fin==1){
            $coins = $unusedCoins;
        }else{
            $coins = rand(0, (($unusedCoins) / $notUsedNum));
        }
        return $coins;
    }
}
