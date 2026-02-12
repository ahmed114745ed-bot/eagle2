<?php

namespace Utd\RoomCup\Http\Controllers\Api;

use App\Helpers\Common;
use App\Models\Setting;
use App\Support\PackageHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Utd\RoomCup\Entities\RoomCupReward;
use Utd\RoomCup\Entities\RoomCupTarget;
use Utd\RoomCup\Http\Resources\RoomCupRewardResource;

class RoomCupController extends Controller
{
    public function myReward($roomId, Request $request)
    {
        if (! PackageHelper::isInstalled('room')) {
            return Common::apiResponse(0, 'Room package not installed', null, 400);
        }

        $roomClass = \Utd\Room\Entities\Room::class;
        $user = $request->user();
        $room = $roomClass::find($roomId);

        if (! $room) {
            return Common::apiResponse(0, 'Room not found', null, 404);
        }

        $check = $this->checkAdmin($room, $user->id);
        if ($user->id !== $room->uid && ! $check) {
            return Common::apiResponse(0, 'You do not have permission', null, 444);
        }

        $rewards = RoomCupReward::where('room_id', $roomId)->where('user_id', $user->id)->get();

        return Common::apiResponse(true, '', RoomCupRewardResource::collection($rewards), 200);
    }

    public function checkAdmin($room, $admin_id)
    {
        $roomAdmin = $room->room_admin;
        $adm_arr = ($roomAdmin === '') ? [] : explode(',', trim($roomAdmin));
        if (count($adm_arr) > 0 && $adm_arr[0] === '') {
            unset($adm_arr[0]);
        }
        $adm_arr = array_unique($adm_arr);

        return in_array($admin_id, $adm_arr);
    }

    public function roomAdministratorManagement($roomId, Request $request)
    {
        if (! PackageHelper::isInstalled('room')) {
            return Common::apiResponse(0, 'Room package not installed', null, 400);
        }

        $totalRoomGiftClass = \Utd\Room\Entities\TotalRoomGift::class;
        $roomClass = \Utd\Room\Entities\Room::class;

        $langCode = $request->header('X-localization', 'en');
        $settings = $this->getSettings();
        $type = $settings['type'] ?? 'daily';
        $totals = RoomCupReward::where('room_id', $roomId);
        $currentQueryTotal = $this->period(clone $totals, 'current', $type);
        $currentQueryTotal = $currentQueryTotal->select('type', DB::raw('SUM(amount) as total_amount'))->groupBy('type')
            ->pluck('total_amount', 'type');
        $trophyQuery = $totalRoomGiftClass::where('room_id', $roomId);

        $currentQuery = $this->period(clone $trophyQuery, 'current', $type);
        $currentData = $currentQuery->with(['room.owner', 'room.level'])
            ->select(
                'room_id',
                DB::raw('SUM(current_total) as total_current'),
                DB::raw('SUM(number_of_visitors) as total_visitors')
            )
            ->groupBy('room_id')
            ->first();

        $lastQuery = $this->period(clone $trophyQuery, 'last', $type);
        $lastData = $lastQuery->select(
            DB::raw('SUM(current_total) as total_current'),
        )->first();

        $data = [
            'room_reward' => [
                'owner' => $currentQueryTotal['owner'] ?? 0,
                'admins' => $currentQueryTotal['admin'] ?? 0,
            ],
            'trophies' => [
                'level' => @$currentData->room->level->level ?? 0,
                'type' => $type,
                'current' => [
                    'total_current' => $currentData->total_current ?? 0,
                    'total_visitors' => $currentData->total_visitors ?? 0,
                ],
                'last' => [
                    'total_current' => $lastData->total_current ?? 0,
                ],
            ],
            'room' => [
                'admin_count' => count(array_filter(explode(',', @$currentData->room->room_admin))),
            ],
            'link' => url('/cup-targets-view?lang='.$langCode),
        ];

        return Common::apiResponse(true, '', $data, 200);
    }

    public function period($builder, $key, $type)
    {
        $now = now();
        switch ($type) {
            case 'daily':
                if ($key === 'last') {
                    $date = $now->copy()->subDay();
                } else {
                    $date = $now;
                }
                $builder->whereDay('created_at', $date->day)
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year);
                break;

            case 'monthly':
                if ($key === 'last') {
                    $date = $now->copy()->subMonth();
                } else {
                    $date = $now;
                }
                $builder->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year);
                break;

            case 'weekly':
                if ($key === 'last') {
                    $start = $now->copy()->subWeek()->startOfWeek();
                    $end = $now->copy()->subWeek()->endOfWeek();
                } else {
                    $start = $now->copy()->startOfWeek();
                    $end = $now->copy()->endOfWeek();
                }
                $builder->whereBetween('created_at', [$start, $end]);
                break;

            default:
                throw new Exception('Time period not defined in settings.');
        }

        return $builder;
    }

    public function cupTargetHtml(Request $request)
    {
        $lang = $request->get('lang', 'en');
        app()->setLocale($lang);
        $cupTargets = RoomCupTarget::get();

        return view('roomcup::cup_target', compact('cupTargets'));
    }

    private function getSettings(): array
    {
        $default = [
            'enabled' => true,
            'interval_minutes' => 60,
            'type' => 'daily',
        ];

        $settings = [];

        foreach ($default as $key => $defaultValue) {
            $cacheKey = 'roomcup_'.$key;
            $value = Cache::get($cacheKey);

            if ($value === null) {
                $setting = Setting::where('key', $cacheKey)->first();
                $value = $setting ? $setting->value : $defaultValue;
                Cache::put($cacheKey, $value, now()->addDays(30));
            }

            $settings[$key] = $value;
        }

        return $settings;
    }
}
