<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Agency;
use App\Models\AgencySallary;
use App\Models\Bd;
use App\Models\Country;
use App\Models\GiftLog;
use App\Models\Room;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Request;
use KevinSoft\MultiLanguage\MultiLanguage;

class SuperAdminCountryController extends Controller
{
    public function index($id)
    {
        $country = Country::findOrFail($id);
        $countryID = $country->id;

        $onlineUsers = User::select(['id', 'country_id', 'online'])->where([
            'country_id' => $countryID,
            'online' => 1,
        ])->count();

        $superAdmin = Admin::where([
            'country_id' => $countryID,
            'type' => 'super_admin',
        ])->first();

        $topSenders = GiftLog::whereHas(
            'sender',
            fn($q) =>
            $q->where('country_id', $countryID)
        )
            ->selectRaw('sender_id, SUM(giftPrice * giftNum) as total_sent')
            ->groupBy('sender_id')
            ->orderByDesc('total_sent')
            ->take(3)
            ->with('sender:id,name')
            ->get()
            ->filter(fn($s) => $s->total_sent > 0);

        $topRooms = Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
            ->with(['owner:id,name,country_id'])
            ->withCount('roomVisitors')
            ->orderByDesc('room_visitors_count')
            ->take(3)
            ->get(['id', 'name', 'uid']);

        $topAgencies = Agency::where('country_id', $countryID)
            ->withCount('members')
            ->orderByDesc('members_count')
            ->take(3)
            ->get(['id', 'name']);

        $topAgencySenders = GiftLog::whereHas(
            'sender',
            fn($q) =>
            $q->where('country_id', $countryID)
                ->whereHas('agency', fn($a) => $a->where('country_id', $countryID))
        )
            ->selectRaw('sender_id, SUM(giftPrice * giftNum) as total_sent')
            ->groupBy('sender_id')
            ->orderByDesc('total_sent')
            ->take(3)
            ->with('sender:id,name')
            ->get()
            ->filter(fn($s) => $s->total_sent > 0);

        $topBds = Bd::where('country_id', $countryID)->whereHas('agencies', function ($a) use ($countryID) {
            $a->where('country_id', $countryID)
                ->whereHas('members');
        })
            ->withCount(['agencies as member_count' => function ($a) use ($countryID) {
                $a->where('country_id', $countryID)
                    ->withCount('members');
            }])
            ->get(['id', 'name']);

        $topChargeAgencies = AgencySallary::whereHas('agency', function ($q) use ($countryID) {
            $q->where('country_id', $countryID)
                ->where('type', 2);
        })
            ->select(
                'agency_id',
                DB::raw('SUM(sallary) - SUM(cut_amount) AS total_due')
            )
            ->where('is_paid', 0)
            ->where(DB::raw('CONCAT(year,"-",month)'), '<=', now()->year . '-' . now()->month)
            ->groupBy('agency_id')
            ->havingRaw('total_due > 0')
            ->orderByDesc('total_due')
            ->take(3)
            ->with('agency:id,name,type')
            ->get();

        return view('super_admin_country', compact([
            'country',
            'superAdmin',
            'onlineUsers',
            'topSenders',
            'topRooms',
            'topAgencies',
            'topAgencySenders',
            'topBds',
            'topChargeAgencies',
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
