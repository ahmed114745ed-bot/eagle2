<?php

namespace Modules\SuperAdmin\Http\Controllers;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Agency;
use App\Models\AgencySallary;
use App\Models\Bd;
use App\Models\Charge;
use App\Models\CoinGameUser;
use App\Models\CoinGameUserMergedMonthly;
use App\Models\Country;
use App\Models\GiftLog;
use App\Models\GiftRanking;
use App\Models\Room;
use Modules\SuperAdmin\Entities\SuperAdmin;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Request;
use KevinSoft\MultiLanguage\MultiLanguage;

class SuperAdminCountryController extends Controller
{
    public function index($id)
    {
        $country = Country::findOrFail($id);
        $countryID = $country->id;

        $timezone = Common::timeZone();
        $from = Carbon::now($timezone)->subDays(30)->startOfDay();
        $to   = Carbon::now($timezone)->endOfDay();

        $onlineUsers = User::select(['id', 'country_id', 'online'])->where([
            'country_id' => $countryID,
            'online' => 1,
        ])->count();

        $superAdmin = SuperAdmin::with('appUser')->where(['country_id' => $countryID])->first();

        $topRooms = Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
            ->with(['owner:id,name,country_id'])
            ->withCount(['roomVisitors' => function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            }])
            ->orderByDesc('room_visitors_count')
            ->take(3)
            ->get(['id', 'name', 'uid']);

        $topSenders = GiftLog::whereHas(
            'sender',
            fn($q) =>
            $q->where('country_id', $countryID)
        )
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('sender_id, SUM(giftPrice * giftNum) as total_sent')
            ->groupBy('sender_id')
            ->orderByDesc('total_sent')
            ->take(3)
            ->with([
                'sender:id,name,country_id',
                'sender.profile:id,user_id,avatar'
            ])
            ->get()
            ->filter(fn($s) => $s->total_sent > 0);

        $topReceivers = GiftLog::whereHas(
            'receiver',
            fn($q) =>
            $q->where('country_id', $countryID)
        )
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('receiver_id, SUM(giftPrice * giftNum) as total_sent')
            ->groupBy('receiver_id')
            ->orderByDesc('total_sent')
            ->take(3)
            ->with([
                'receiver:id,name,country_id',
                'receiver.profile:id,user_id,avatar'
            ])
            ->get()
            ->filter(fn($s) => $s->total_sent > 0);

        $topAgencies = Agency::where('country_id', $countryID)
            ->withCount('members')
            ->orderByDesc('members_count')
            ->take(3)
            ->get(['id', 'name']);

        $topChargeAgencies = Charge::where('charger_type', 'agency')
            ->whereHas('senderShippingAgency', function ($q) use ($countryID) {
                $q->where('country_id', $countryID);
            })
            ->whereBetween('created_at', [$from, $to])
            ->with([
                'senderShippingAgency:id,name,country_id,img'
            ])
            ->orderByDesc('amount')
            ->take(3)
            ->get();

        $topBds = Bd::whereHas('agencies', function ($a) use ($countryID) {
            $a->where('country_id', $countryID)
                ->whereHas('members');
        })
            ->withCount(['agencies as total_members' => function ($agency) use ($countryID) {
                $agency->where('country_id', $countryID)
                    ->withCount('members');
            }])
            ->orderByDesc('total_members')
            ->take(3)
            ->get(['id', 'name']);

        $topGamers = CoinGameUser::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('type', 1)
            ->whereHas('user', fn($q) => $q->where('country_id', $countryID))
            ->with([
                'user:id,name,country_id',
                'user.profile:id,user_id,avatar'
            ])
            ->orderByDesc('coins')
            ->limit(3)
            ->get();

        return view('super_admin_country', compact([
            'country',
            'superAdmin',
            'onlineUsers',
            'topSenders',
            'topRooms',
            'topAgencies',
            'topReceivers',
            'topBds',
            'topChargeAgencies',
            'topGamers'
        ]));
    }

    public function locale()
    {
        $locale = Request::input('locale');
        $languages = MultiLanguage::config('languages');

        $cookie_name = MultiLanguage::config('cookie-name', 'locale');

        if (array_key_exists($locale, $languages)) {

            return response('ok')->cookie($cookie_name, $locale);
        }
    }


    public function index2($id)
    {
        $country = Country::findOrFail($id);

        return view('superAdmin.super_admin_country', compact('country'));
    }

    public function getStats(Request $request, $id)
    {
        $country = Country::findOrFail($id);
        $cacheKey = "country_stats_{$id}";

        // Cache for 5 minutes
        $stats = \Cache::remember($cacheKey, 300, function () use ($country) {
            return $this->fetchCountryStats($country);
        });

        return response()->json($stats);
    }

    private function fetchCountryStats(Country $country)
    {
        $countryID = $country->id;
        $timezone = config('app.timezone', 'UTC');
        $from = Carbon::now($timezone)->subDays(30)->startOfDay();
        $to = Carbon::now($timezone)->endOfDay();

        // Run queries in parallel using lazy collections where possible
        return [
            'onlineUsers' => $this->getOnlineUsers($countryID),
            'superAdmin' => $this->getSuperAdmin($countryID),
            'topRooms' => $this->getTopRooms($countryID, $from, $to),
            'topSenders' => $this->getTopSenders($countryID, $from, $to),
            'topReceivers' => $this->getTopReceivers($countryID, $from, $to),
            'topAgencies' => $this->getTopAgencies($countryID),
            'topChargeAgencies' => $this->getTopChargeAgencies($countryID, $from, $to),
            'topBds' => $this->getTopBds($countryID),
            'topGamers' => $this->getTopGamers($countryID, $from, $to),
        ];
    }

    private function getOnlineUsers($countryID)
    {
        return User::where('country_id', $countryID)
            ->where('online', 1)
            ->count();
    }

    private function getSuperAdmin($countryID)
    {
        return Admin::where('country_id', $countryID)
            ->where('type', 'super_admin')
            ->select('id', 'name', 'country_id')
            ->with('user:id,name')
            ->first();
    }

    private function getTopRooms($countryID, $from, $to)
    {
        return Room::select('rooms.id', 'rooms.room_name', 'rooms.uid', 'rooms.room_cover')
            ->join('users', 'rooms.uid', '=', 'users.id')
            ->where('users.country_id', $countryID)
            ->with('owner:id,name,country_id')
            ->withCount(['roomVisitors' => function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            }])
            ->orderByDesc('room_visitors_count')
            ->limit(3)
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_name' => $room->room_name,
                    'room_cover' => $room->room_cover,
                    'room_visitors_count' => $room->room_visitors_count,
                    'owner' => $room->owner,
                ];
            });
    }

    private function getTopSenders($countryID, $from, $to)
    {
        return GiftLog::select('sender_id')
            ->selectRaw('SUM(giftPrice * giftNum) as total_sent')
            ->join('users', 'gift_logs.sender_id', '=', 'users.id')
            ->where('users.country_id', $countryID)
            ->whereBetween('gift_logs.created_at', [$from, $to])
            ->groupBy('sender_id')
            ->having('total_sent', '>', 0)
            ->orderByDesc('total_sent')
            ->limit(3)
            ->with([
                'sender:id,name,country_id',
                'sender.profile:id,user_id,avatar'
            ])
            ->get();
    }

    private function getTopReceivers($countryID, $from, $to)
    {
        return GiftLog::select('receiver_id')
            ->selectRaw('SUM(giftPrice * giftNum) as total_sent')
            ->join('users', 'gift_logs.receiver_id', '=', 'users.id')
            ->where('users.country_id', $countryID)
            ->whereBetween('gift_logs.created_at', [$from, $to])
            ->groupBy('receiver_id')
            ->having('total_sent', '>', 0)
            ->orderByDesc('total_sent')
            ->limit(3)
            ->with([
                'receiver:id,name,country_id',
                'receiver.profile:id,user_id,avatar'
            ])
            ->get();
    }

    private function getTopAgencies($countryID)
    {
        return Agency::select('id', 'name', 'img')
            ->where('country_id', $countryID)
            ->withCount('members')
            ->orderByDesc('members_count')
            ->limit(3)
            ->get();
    }

    private function getTopChargeAgencies($countryID, $from, $to)
    {
        return Charge::select('charges.*')
            ->where('charger_type', 'agency')
            ->join('agencies', 'charges.charger_id', '=', 'agencies.id')
            ->where('agencies.country_id', $countryID)
            ->whereBetween('charges.created_at', [$from, $to])
            ->with('senderShippingAgency:id,name,country_id,img')
            ->orderByDesc('amount')
            ->limit(3)
            ->get();
    }

    private function getTopBds($countryID)
    {
        return Bd::whereHas('agencies', function ($a) use ($countryID) {
            $a->where('country_id', $countryID)
                ->whereHas('members');
        })
            ->withCount(['agencies as total_members' => function ($agency) use ($countryID) {
                $agency->where('country_id', $countryID)
                    ->withCount('members');
            }])
            ->orderByDesc('total_members')
            ->take(3)
            ->get(['id', 'name']);
    }

    private function getTopGamers($countryID, $from, $to)
    {
        return CoinGameUser::select('coin_game_users.*')
            ->join('users', 'coin_game_users.user_id', '=', 'users.id')
            ->where('users.country_id', $countryID)
            ->where('coin_game_users.type', 1)
            ->whereBetween('coin_game_users.created_at', [$from, $to])
            ->with([
                'user:id,name,country_id',
                'user.profile:id,user_id,avatar'
            ])
            ->orderByDesc('coins')
            ->limit(3)
            ->get();
    }
}
