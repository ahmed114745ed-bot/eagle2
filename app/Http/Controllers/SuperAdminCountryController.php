<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
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

        $superAdmin = Admin::where([
            'country_id' => $countryID,
            'type' => 'super_admin',
        ])->first();

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
            ->withCount(['agencies as member_count' => function ($a) use ($countryID) {
                $a->where('country_id', $countryID)
                    ->withCount('members');
            }])
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
}
