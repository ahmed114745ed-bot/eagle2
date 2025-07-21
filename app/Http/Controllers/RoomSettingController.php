<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\Config;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class RoomSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $paidRoom = Config::where('name', 'paid_room')->first();
        $paidRoomAmount = Config::where('name', 'paid_room_amount')->first();
        $data = [
            $paidRoom->name => (bool)$paidRoom->value ?? false,
            $paidRoomAmount->name => $paidRoomAmount->value ?? 0,
        ];

        return Common::apiResponse(true, '', $data, 200);
    }
}
