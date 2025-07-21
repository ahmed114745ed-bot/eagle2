<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\Config;
use App\Models\Setting;

class RoomSettingController extends Controller
{
    public function show(): \Illuminate\Http\JsonResponse
    {
        $paidRoom = Config::where('name', 'paid_room')->first();
        $paidRoomAmount = Config::where('name', 'paid_room_amount')->first();
        $data = [
            $paidRoom->name => (bool)$paidRoom->value,
            $paidRoomAmount->name => $paidRoomAmount->value,
        ];

        return Common::apiResponse(true, '', $data, 200);
    }
}
