<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\PackService;
use App\Http\Controllers\Controller;


class PackController extends Controller
{

    public function __construct( private PackService $packService)
    {
       
    }

    public function hide(Request $request)
    {
        $user         = $request->user();

        $privilegeArr = [
            'has_color_name' => 18,
            'anonymous'      => 17,
            'country'        => 13,
            'last_active'    => 20,
            'visit'          => 19,
            'room'           => 16,
            'sound_effect'   => 21
        ];
        $type         = $request->type;
        $this->changePackMode($type, $privilegeArr, $user, true);
        return Common::apiResponse(1, 'ok', null, 200);
    }

    public function un_hide(Request $request)
    {
        $user = $request->user();
        $privilegeArr = [
            'has_color_name' => 18,
            'anonymous'      => 17,
            'country'        => 13,
            'last_active'    => 20,
            'visit'          => 19,
            'room'           => 16,
            'sound_effect'   => 21
        ];
        $type         = $request->type;
        $this->changePackMode($type, $privilegeArr, $user, false);

        return Common::apiResponse(1, 'ok', null, 200);
    }

    public function changePackMode($type, $privilegeArr, User $user, $isAvailable)
    {

        try{
            $this->packService->changePackMode($type, $privilegeArr,$user,$isAvailable);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    
    }
}
