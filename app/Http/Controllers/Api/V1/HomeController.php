<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\PK;
use Carbon\Carbon;
use App\Models\Vip;
use App\Models\OVip;
use App\Models\Pack;
use App\Models\Room;
use App\Models\User;
use App\Models\Ware;

use App\Models\Image;
use App\Helpers\Agora;
use App\Models\Ticket;
use App\Helpers\Common;
use App\Models\CoinLog;
use App\Models\Country;
use App\Models\GiftLog;
use App\Models\UserVip;
use App\Models\Exchange;
use App\Models\LiveTime;
use App\Models\Background;
use App\Helpers\UserCommon;
use App\Models\ExchangeLog;
use App\Models\RoomCategory;
use App\Models\VipPrivilege;
use Illuminate\Http\Request;
use App\Classes\Packs\AllowPacks;
use App\Tik\Services\HomeService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestBackgroundImage;
use App\Http\Resources\CountryResource;
use App\Http\Resources\Api\V1\TrxResource;
use App\Http\Resources\Api\V1\RoomCategoryResource;

class HomeController extends Controller
{


    public function __construct(private HomeService $homeService) {}


    public function one_page(Request $request)
    {
        $type = $request->type;
        if (!$type)  return Common::apiResponse(0, 'Missing parameters', null, 422);
        $data = DB::table('pages')->where(['type' => $type])->get();
        return Common::apiResponse(1, '', $data);
    }

    public function countVipsold()
    {
        $count = Vip::query()->where('type', 3)->count();
        $vips =  Vip::query()->where('type', 3)->select('id', 'level', 'type', 'img')->get();
        return Common::apiResponse(1, '', ['vip_count' => $count, 'vips' => $vips]);
    }


    public function getTimes(Request $request)
    {

        $userId = $request->user_id ?: Auth::id();


        [$user, $diamonds, $days, $type, $totalTime, $today] = $this->homeService->totalHours($request, $userId);

        return Common::apiResponse(
            1,
            '',
            [
                'diamonds' => (int)$diamonds ?: 0,
                'days'     => (int)$days ?: 0,
                'hours'    => $totalTime ?: 0,
                'today'    => $today,
                'user_extras' => UserCommon::UserStatistic($user->id, $type, true) ?? new \stdClass(),
            ],
            200
        );
    }


    public function openTicket(Request $request)
    {
        if (!$request->contact || !$request->txt) {
            return Common::apiResponse(0, 'missing params');
        }

        $tkt = $this->homeService->openTicket($request);
        $out = [
            'contact' => $tkt->contact_num,
            'txt' => $tkt->problem,
            'description' => $tkt->description,
            'image' => $tkt->img,
        ];
        return Common::apiResponse(1, 'done', $out, 200);
    }

    public function sendToZego(Request $request)
    {
        $ms = [
            'messageContent' => [
                'message' => $request->message,
            ]
        ];
        $ex = json_decode($request->ext, true);
        if (is_array($ex)) {
            foreach ($ex as $k => $value) {
                $ms['messageContent'][$k] = $value;
            }
        }

        $json = json_encode($ms);
        $user_id = $request->user_id ?: 0;
        $action = $request->action ?: 'SendCustomCommand';
        $room = Room::query()->where('uid', $request->owner_id)->first();
        if (!$room) return Common::apiResponse(0, 'not found', null, 404);
        Common::sendToZego($action, $room->id, $user_id, $json);
        return Common::apiResponse(1, 'done', null, 201);
    }


    public function getImages()
    {
        $type = \request()->type ?? '';
        if ($type == '2') {
            $data = $this->getGamesImages();
            return Common::apiResponse(1, 'ok', $data, 200);
        }


        [$pk_images, $vip_images] =  $this->homeService->imageIndex();
        $data = [
            'pk_images' => $pk_images,
            'vip_images' => $vip_images,
        ];
        return Common::apiResponse(1, 'ok', $data, 200);
    }

    public function check_wapel(Request $request)
    {

        $user = $request->user();

        try {
            [$level, $expire, $ware, $roomId, $wapel] =   $this->homeService->wapel($user->id, $request->owner_id);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        if ($wapel) {

            $ms = [
                'messageContent' => [
                    'msg' => 'PobUp',
                    'uId' => $user->id,
                    //'num'=>$ware->use_num,
                    'my_msg' => $request->message,

                    'name' => $user->name,
                    'image' => $user->profile->avatar,

                    'VIP' => $level,
                    'check_wapel' => $expire,
                ]
            ];
            $json = json_encode($ms);
            Common::sendToZego('SendCustomCommand', @$roomId, $user->id, $json);

            return Common::apiResponse(1, 'has wapel', @$ware, 200);
        } else {
            return Common::apiResponse(0, 'no wapel', null, 404);
        }
    }



    public function getUserHides(Request $request)
    {
        $user         = $request->user();
        $privilegeArr = [
            'has_color_name' => 18,
            'anonymous'      => 17,
            'country'        => 13,
            'last_active'    => 20,
            'visit'          => 19,
            'room'           => 16,
            'sound_effect'    => 21
        ];
        $packsClass   = new AllowPacks($user, $privilegeArr);
        $data = $packsClass->getData();

        return Common::apiResponse(1, 'ok', $data, 200);
    }




    /**
     * @param mixed $user_hours
     * @return string
     */
    public function timeDoubleToString(mixed $user_hours): string
    {

        $user_hours    = (float)($user_hours * 100 / 60);
        $intHours      = (int)$user_hours;
        $intMinutes    = (int)(($user_hours - $intHours) * 100);
        if ($intMinutes >= 60) {
            $intMinutes -= 60;
            $intHours += 1;
        }
        return sprintf('%02d:%02d:00', $intHours, $intMinutes);
    }

    /**
     * @return array[]
     */
    private function getGamesImages(): array
    {
        return [
            'dice'     => [
                'id'    => 1,
                'image' => 'extradata/room_default_dice.svga'
            ],
            'rps'      => [
                'id'    => 2,
                'image' => 'extradata/room_finger_guessing.svga'
            ],
            'gift_box' => [
                'id'    => 3,
                'image' => 'extradata/Icon_G_Box.svga'
            ]
        ];
    }

    public function check_if_friend(Request $request)
    {
        $me = $request->user();
        $user_id = $request->user_id;
        if (in_array($user_id, $me->friends_ids()->toArray())) {
            return Common::apiResponse(1, 'exists', true);
        }
        return Common::apiResponse(1, 'does not exists', false);
    }
}
