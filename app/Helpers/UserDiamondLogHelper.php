<?php

namespace App\Helpers;

use App\Enums\UserDiamondLogType;
use App\Models\UserDiamondLog;


class UserDiamondLogHelper
{
    public static function logByType(
        int $userId,
        float $amount,
        float $diamondBefore,
        UserDiamondLogType $type,
        ?int $getById = null,
        ?float $coin = null,

    ): void {
        $meta = $type->meta();

        $logData = [
            'user_id'       => $userId,
            'amount'        => $amount,
            'diamond_before' => $diamondBefore,
            'type'          => $type->value,
            'sub_type'      => $meta['sub_type'],
            'item_name'     =>  $meta['item_name'],
            'get_by_id' => $getById,
            'coin'     => $coin,
        ];


        UserDiamondLog::create($logData);
    }
}
