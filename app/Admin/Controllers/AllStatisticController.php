<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Charge;
use App\Models\CoinLog;
use Encore\Admin\Widgets\Box;
use App\Models\User;
use App\Models\GameWallet;
use App\Models\UserSallary;
use App\Models\AgencySallary;
use Encore\Admin\Layout\Content;
use App\Models\GameChargeHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Utd\Bd\Entities\Bd;
use Carbon\Carbon;
use Utd\Chat\Entities\ChatMessage;
use Utd\Room\Entities\Room;
use App\Models\Agency;
use Utd\Gifts\Entities\GiftLog;
use App\Models\LiveTime;
use App\Models\UserTarget;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;

use Utd\AreaManager\Entities\AreaManager;
use App\Support\PackageHelper;
use App\Models\CoinGameUserDailyAggregated;
use Carbon\CarbonPeriod;
use Utd\UsersWallet\Entities\WalletLog;

class AllStatisticController extends MainController
{
    public $permission_name = 'dashboard';

    protected function countryId()
    {
        return session('filter_country_id');
    }

    public static function countryIds(): array
    {
        $filterCountryId = session('filter_country_id');
        if (!empty($filterCountryId)) {
            return [(int)$filterCountryId];
        }

        $adminId = session('area_manager_id') ?? auth()->id();

        if (!$adminId) {
            return [];
        }

        $authAdmin = AreaManager::find($adminId);

        if (!$authAdmin) {
            return [];
        }

        $sessionCountryId = session('area_manager_country_id');
        if (!empty($sessionCountryId)) {
            return (array)$sessionCountryId;
        }

        if (method_exists($authAdmin, 'countriesQuery')) {
            return $authAdmin->countriesQuery()->pluck('id')->toArray();
        }

        return [];
    }

    public function index(Content $content)
    {
        return parent::index(
            $content
                ->title(__('Home'))
                ->description(__('General Statistics'))
                ->row(function (Row $row) {
                    $row->column(12, view('admin.dashboard.chart'));
                })
        );
    }

    public function getTopFollowers(Request $request): JsonResponse
    {
        $countryID = $this->countryId();

        $topUsersByFollowers = User::withCount('followers')
            ->with('profile')
            ->when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->orderByDesc('followers_count')
            ->take(10)
            ->get()
            ->map(function ($user) {
                $avatar = $user->profile?->avatar;
                $user->avatar_url = $avatar ? getImagePath($avatar) : asset('images/businessman-icon.jpg');
                return $user;
            });

        return response()->json($topUsersByFollowers);
    }

    public function peakHours(Request $request)
    {
        $period = $request->get('period', 'day');

        $query = DB::table('live_times')
            ->join('users', 'live_times.uid', '=', 'users.id');

        if ($period === 'day') {
            $query->selectRaw("FROM_UNIXTIME(live_times.start_time, '%H') as label, COUNT(*) as total")
                ->whereRaw("DATE(FROM_UNIXTIME(live_times.start_time)) = CURDATE()")
                ->groupBy('label');
        } elseif ($period === 'week') {
            $query->selectRaw("DATE(FROM_UNIXTIME(live_times.start_time)) as label, COUNT(*) as total")
                ->whereRaw("YEARWEEK(FROM_UNIXTIME(live_times.start_time)) = YEARWEEK(CURDATE())")
                ->groupBy('label');
        } elseif ($period === 'month') {
            $query->selectRaw("DATE(FROM_UNIXTIME(live_times.start_time)) as label, COUNT(*) as total")
                ->whereRaw("YEAR(FROM_UNIXTIME(live_times.start_time)) = YEAR(CURDATE())
                            AND MONTH(FROM_UNIXTIME(live_times.start_time)) = MONTH(CURDATE())")
                ->groupBy('label');
        }

        $rows = $query->orderBy('label')->get();

        return response()->json([
            'success' => true,
            'labels' => $rows->pluck('label'),
            'data' => $rows->pluck('total'),
        ]);
    }

    public function onlineStats()
    {
        $online = User::where('online', 1)->count();
        $offline = User::where('online', 0)->count();


        return response()->json(data: [
            'online' => $online,
            'offline' => $offline,
        ]);
    }

    public function roomsActivity(Request $request)
    {
        $period = $request->get('period', 'day');

        if ($period === 'day') {
            $dates = collect(range(0, 6))
                ->map(fn($i) => now()->subDays($i)->format('Y-m-d'))
                ->reverse()
                ->values();

            $newRoomsData = Room::whereDate('created_at', '>=', now()->subDays(6))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $newRooms = $dates->map(fn($d) => $newRoomsData[$d] ?? 0);

            $activeOwners = \DB::table('users')
                ->join('live_times', 'users.id', '=', 'live_times.uid')
                ->whereDate('live_times.created_at', '>=', now()->subDays(6))
                ->selectRaw('DATE(live_times.created_at) as date, COUNT(DISTINCT users.id) as active_owners')
                ->groupBy('date')
                ->pluck('active_owners', 'date');

            $totalRooms = Room::count();

            $inactiveRooms = $dates->map(fn($d) => $totalRooms - ($activeOwners[$d] ?? 0));

            $labels = $dates;
        } elseif ($period === 'week') {
            $weeks = collect(range(0, 3))
                ->map(fn($i) => now()->subWeeks($i)->format('o-\WW'))
                ->reverse()
                ->values();

            $newRoomsData = Room::where('created_at', '>=', now()->subWeeks(3)->startOfWeek())
                ->selectRaw("YEAR(created_at) as year, WEEK(created_at,1) as week, COUNT(*) as total")
                ->groupBy('year', 'week')
                ->get()
                ->mapWithKeys(fn($r) => [sprintf('%d-W%02d', $r->year, $r->week) => $r->total]);

            $newRooms = $weeks->map(fn($w) => $newRoomsData[$w] ?? 0);

            $activeOwners = \DB::table('users')
                ->join('live_times', 'users.id', '=', 'live_times.uid')
                ->whereDate('live_times.created_at', '>=', now()->subWeeks(3)->startOfWeek())
                ->selectRaw("YEAR(live_times.created_at) as year, WEEK(live_times.created_at,1) as week, COUNT(DISTINCT users.id) as active_owners")
                ->groupBy('year', 'week')
                ->get()
                ->mapWithKeys(fn($r) => [sprintf('%d-W%02d', $r->year, $r->week) => $r->active_owners]);

            $totalRooms = Room::count();

            $inactiveRooms = $weeks->map(fn($w) => $totalRooms - ($activeOwners[$w] ?? 0));

            $labels = $weeks;
        } elseif ($period === 'month') {
            // last 6 months
            $months = collect(range(0, 5))
                ->map(fn($i) => now()->subMonths($i)->format('Y-m'))
                ->reverse()
                ->values();

            $newRoomsData = Room::where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at,'%Y-%m') as ym, COUNT(*) as total")
                ->groupBy('ym')
                ->pluck('total', 'ym');

            $newRooms = $months->map(fn($m) => $newRoomsData[$m] ?? 0);

            $activeOwners = \DB::table('users')
                ->join('live_times', 'users.id', '=', 'live_times.uid')
                ->where('live_times.created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw("DATE_FORMAT(live_times.created_at,'%Y-%m') as ym, COUNT(DISTINCT users.id) as active_owners")
                ->groupBy('ym')
                ->pluck('active_owners', 'ym');

            $totalRooms = Room::count();

            $inactiveRooms = $months->map(fn($m) => $totalRooms - ($activeOwners[$m] ?? 0));

            $labels = $months;
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'newRooms' => $newRooms,
            'inactiveRooms' => $inactiveRooms,
        ]);
    }

    public function topUsersData(Request $request)
    {
        $countryID = $this->countryId();

        $topUsers = LiveTime::query()
            ->whereHas('user', function ($q) use ($countryID) {
                $q->when($countryID, fn($q) => $q->where('country_id', $countryID));
            })
            ->selectRaw('uid, SUM(hours) as total_hours')
            ->groupBy('uid')
            ->havingRaw('SUM(hours) >= 1')
            ->orderByDesc('total_hours')
            ->take(10)
            ->get();

        $labels = User::whereIn('id', $topUsers->pluck('uid'))
            ->pluck('name');

        $data = $topUsers->pluck('total_hours');

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    public function topUsersVisits(Request $request)
    {
        $topUsers = User::select('id', 'name')
            ->withCount(['liveTimes as total_hours' => function ($q) {
                $q->select(DB::raw("SUM(hours)"))
                    ->where('start_time', '>=', now()->subMonth());
            }])
            ->having('total_hours', '>', 0)
            ->orderByDesc('total_hours')
            ->take(10)
            ->get();
        return response()->json([
            'labels' => $topUsers->pluck('name'),
            'data' => $topUsers->pluck('total_hours')
        ]);
    }


    protected function comparisonUserSignUp()
    {
        $currMonth = now()->month;
        $prevMonth = now()->subMonth()->month;
        $countryID = $this->countryId();

        $signups = User::when($countryID, function ($query, $countryID) {
            return $query->where('country_id', $countryID);
        })
            ->selectRaw("
                                YEAR(created_at) as year,
                                MONTH(created_at) as month,
                                FLOOR((DAY(created_at)-1)/7)+1 as week_of_month,
                                COUNT(*) as total
                            ")
            ->whereIn(DB::raw('MONTH(created_at)'), [$currMonth, $prevMonth])
            ->groupBy('year', 'month', 'week_of_month')
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('week_of_month')
            ->get();

        $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];

        $dataCurrent = [];
        $dataPrevious = [];

        foreach (range(1, 4) as $week) {
            $dataCurrent[] = $signups->where('month', $currMonth)->where('week_of_month', $week)->sum('total');
            $dataPrevious[] = $signups->where('month', $prevMonth)->where('week_of_month', $week)->sum('total');
        }

        $currMonthName = Carbon::create()->month($currMonth)->translatedFormat('F');
        $prevMonthName = Carbon::create()->month($prevMonth)->translatedFormat('F');

        return response()->json([
            'labels' => $labels,
            'dataCurrent' => $dataCurrent,
            'dataPrevious' => $dataPrevious,
            'currentMonth' => $currMonthName,
            'previousMonth' => $prevMonthName,
        ]);
    }

    protected function distributionRooms()
    {
        $countryID = $this->countryId();
        $roomsWithPk = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->when($countryID, function ($query, $countryID) {
                return $query->where('country_id', $countryID);
            });
        })
            ->has('lastPk')
            ->count();

        $audioRooms = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->when($countryID, function ($query, $countryID) {
                return $query->where('country_id', $countryID);
            });
        })
            ->where('type', 'audio')
            ->count();

        $liveRooms = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->when($countryID, function ($query, $countryID) {
                return $query->where('country_id', $countryID);
            });
        })
            ->where('type', 'live')
            ->count();

        $inactiveRooms = Room::whereHas('owner', fn($q) => $q->when($countryID, function ($query, $countryID) {
            return $query->where('country_id', $countryID);
        }))
            ->whereDoesntHave('roomVisitors')
            ->count();

        $roomStats = [
            __('Rooms with PK') => $roomsWithPk,
            __('Audio Rooms') => $audioRooms,
            __('Live Rooms') => $liveRooms,
            __('Inactive Rooms') => $inactiveRooms,
        ];

        return response()->json([
            'labels' => array_keys($roomStats),
            'data' => array_values($roomStats),
        ]);
    }

    protected function topRoomGifts()
    {
        $countryID = $this->countryId();
        $topGiftedRooms = Room::whereHas('owner', fn($q) => $q->when($countryID, function ($query, $countryID) {
            return $query->where('country_id', $countryID);
        }))
            ->with('owner')
            ->withSum('gifts', 'giftPrice')
            ->orderByDesc('gifts_sum_gift_price')
            ->take(10)
            ->get()
            ->filter(fn($room) => $room->gifts_sum_gift_price > 0);

        $labels = $topGiftedRooms->map(fn($room) => $room->owner->name ?? 'Unknown');
        $data = $topGiftedRooms->pluck('gifts_sum_gift_price');
        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    protected function averageActiveRooms()
    {
        $avgSessionRooms = \DB::table('rooms')
            ->join('users', 'rooms.uid', '=', 'users.id')
            ->join('live_times', 'users.id', '=', 'live_times.uid')
            ->where('users.country_id', Auth::user()->country_id)
            ->select(
                'rooms.id',
                'users.name as room_name',
                \DB::raw('AVG(
                                        COALESCE(live_times.hours,
                                            TIMESTAMPDIFF(SECOND, FROM_UNIXTIME(live_times.start_time), FROM_UNIXTIME(live_times.end_time)) / 3600
                                        )
                                    ) as avg_duration')
            )
            ->groupBy('rooms.id', 'users.name')
            ->orderByDesc('avg_duration')
            ->limit(10)
            ->get();

        $labels = $avgSessionRooms->pluck('room_name');
        $data = $avgSessionRooms->pluck('avg_duration');
        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    protected function agencyTarget()
    {
        $countryID = $this->countryId();
        $topAgenciesByTargets = UserTarget::whereHas('agency', fn($q) => $q->when($countryID, function ($query, $countryID) {
            return $query->where('country_id', $countryID);
        }))
            ->where('agency_obtain', '>', 0)
            ->selectRaw('agency_id, COUNT(*) as total_achieved')
            ->groupBy('agency_id')
            ->orderByDesc('total_achieved')
            ->with('agency:id,name')
            ->take(10)
            ->get();

        $labels = $topAgenciesByTargets->map(fn($t) => $t->agency->name ?? 'Unknown');
        $data = $topAgenciesByTargets->pluck('total_achieved');
        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    protected function topSender()
    {
        $countryID = $this->countryId();
        $topSenders = GiftLog::whereHas(
            'sender',
            fn($q) => $q->when($countryID, function ($query, $countryID) {
                return $query->where('country_id', $countryID);
            })
                ->whereHas('agency', fn($a) => $a->when($countryID, function ($query, $countryID) {
                    return $query->where('country_id', $countryID);
                }))
        )
            ->selectRaw('sender_id, SUM(giftPrice * giftNum) as total_sent')
            ->groupBy('sender_id')
            ->orderByDesc('total_sent')
            ->take(10)
            ->with('sender:id,name')
            ->get()
            ->filter(fn($s) => $s->total_sent > 0);

        $labels = $topSenders->map(fn($s) => $s->sender->name ?? 'Unknown');
        $data = $topSenders->pluck('total_sent');
        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    protected function topReceiver()
    {
        $countryID = $this->countryId();
        $topReceivers = GiftLog::whereHas(
            'receiver',
            fn($q) => $q->when($countryID, function ($query, $countryID) {
                return $query->where('country_id', $countryID);
            })
                ->whereHas('agency', fn($a) => $a->when($countryID, function ($query, $countryID) {
                    return $query->where('country_id', $countryID);
                }))
        )
            ->selectRaw('receiver_id, SUM(giftPrice * giftNum) as total_received')
            ->groupBy('receiver_id')
            ->orderByDesc('total_received')
            ->take(10)
            ->with('receiver:id,name')
            ->get()
            ->filter(fn($s) => $s->total_received > 0);

        $labels = $topReceivers->map(fn($r) => $r->receiver->name ?? 'Unknown');
        $data = $topReceivers->pluck('total_received');
        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    protected function comparisonAgencyTarget()
    {
        $countryID = $this->countryId();
        $achievedAgencies = Agency::when($countryID, function ($query, $countryID) {
            return $query->where('country_id', $countryID);
        })
            ->whereHas('userTarget', function ($q) {
                $q->where('agency_obtain', '>', 0);
            })
            ->count();

        $notAchievedAgencies = Agency::when($countryID, function ($query, $countryID) {
            return $query->where('country_id', $countryID);
        })
            ->count() - $achievedAgencies;

        return response()->json([
            'achieved' => $achievedAgencies,
            'notAchieved' => $notAchievedAgencies,
        ]);
    }

    public function roomStats()
    {
        $countryID = $this->countryId();
        $roomCounts = Room::whereHas('owner.country', fn($q) => $q->where('id', $countryID))
            ->whereHas('roomVisitors')
            ->selectRaw("type, COUNT(*) as total")
            ->groupBy('type')
            ->pluck('total', 'type');


        $liveRooms = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->when($countryID, function ($query, $countryID) {
                return $query->where('country_id', $countryID);
            });
        })
            ->where('type', 'live')
            ->selectRaw('is_live, COUNT(*) as total')
            ->groupBy('is_live')
            ->pluck('total', 'is_live');

        $liveRoomsTrue = $liveRooms[1] ?? 0;
        $liveRoomsFalse = $liveRooms[0] ?? 0;
        return response()->json([
            'audio' => $roomCounts['audio'] ?? 0,
            'live' => $roomCounts['live'] ?? 0,
            'active' => $liveRoomsTrue,
            'inactive' => $liveRoomsFalse,
        ]);
    }

    public function getStats()
    {
        $countryID = $this->countryId();

        $agencyCount = Agency::when($countryID, fn($q) => $q->where('country_id', $countryID))->count();

        $user_salaries = UserSallary::whereHas('user', function ($q) use ($countryID) {
            $q->where('agency_id', '!=', 0)
                ->when($countryID, fn($q) => $q->where('country_id', $countryID))
                ->whereHas('agency', fn($a) => $a->when($countryID, fn($q) => $q->where('country_id', $countryID)));
        })->sum(DB::raw('sallary - cut_amount'));

        $agency_salaries = AgencySallary::whereHas(
            'agency',
            fn($q) => $q->when($countryID, fn($q) => $q->where('country_id', $countryID))
        )->sum(DB::raw('sallary - cut_amount'));

        $activeAgencies = Agency::when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->whereHas('agencySalaries', fn($q) => $q->where('month', now()->month)->where('year', now()->year))
            ->count();

        $newAgenciesToday = Agency::when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->whereDate('created_at', today())
            ->count();

        $newAgenciesMonth = Agency::when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $avgAgencyWallet = Agency::when($countryID, fn($q) => $q->where('country_id', $countryID))->avg('coins');

        $totalMembers = User::when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->where('agency_id', '!=', 0)
            ->whereHas('agency', fn($q) => $q->when($countryID, fn($q) => $q->where('country_id', $countryID)))
            ->count();

        $avgMembersPerAgency = $agencyCount > 0 ? round($totalMembers / $agencyCount) : 0;

        $pendingJoins = Agency::when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->whereHas('joinRequests', fn($q) => $q->where('status', 1))
            ->count();

        $diamondsAchieved = UserSallary::whereHas('user', fn($q) => $q->when($countryID, fn($q) => $q->where('country_id', $countryID)))
            ->whereHas('agency', fn($q) => $q->when($countryID, fn($q) => $q->where('country_id', $countryID)))
            ->sum('achieved_diamond');

        return response()->json([
            'agencyCount' => $agencyCount,
            'user_salaries' => round($user_salaries),
            'agency_salaries' => round($agency_salaries),
            'activeAgencies' => $activeAgencies,
            'newAgenciesToday' => $newAgenciesToday,
            'newAgenciesMonth' => $newAgenciesMonth,
            'avgAgencyWallet' => round($avgAgencyWallet),
            'totalMembers' => $totalMembers,
            'avgMembersPerAgency' => $avgMembersPerAgency,
            'pendingJoins' => $pendingJoins,
            'diamondsAchieved' => $diamondsAchieved,
        ]);
    }


    public function getBdStats()
    {
        if (!PackageHelper::isInstalled('bd')) {
            return response()->json([
                'bdCount' => 0,
                'totalBDSalary' => 0,
                'totalBDCut' => 0,
                'averageAgenciesPerBD' => 0,
            ]);
        }

        $countryID = $this->countryId();

        $bdCount = Bd::when($countryID, fn($q) => $q->where('country_id', $countryID))->count();

        $totalSalaries = Bd::when($countryID, fn($q) => $q->where('country_id', $countryID))
            ->withSum('salaries', 'salary')
            ->withSum('salaries', 'cut_amount')
            ->withCount('agencies')
            ->get();

        $totalBDSalary = $totalSalaries->sum('salaries_sum_salary');
        $totalBDCut = $totalSalaries->sum('salaries_sum_cut_amount');
        $averageAgenciesPerBD = $totalSalaries->avg('agencies_count');

        return response()->json([
            'bdCount' => $bdCount,
            'totalBDSalary' => round($totalBDSalary),
            'totalBDCut' => round($totalBDCut),
            'averageAgenciesPerBD' => round($averageAgenciesPerBD, 2),
        ]);
    }

    public function getBalanceData(Request $request)
    {
        try {
            $date = $request->get("date");

            $balanceQuery = GameWallet::query();
            $balanceDollarQuery = GameChargeHistory::query();

            if ($date != null) {
                $year = substr($date, 0, 4);
                $month = substr($date, 5, 2);
                $balanceQuery->whereMonth("created_at", $month)->whereYear("created_at", $year);
                $balanceDollarQuery->whereMonth("created_at", $month)->whereYear("created_at", $year);
            } else {
                $balanceQuery->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"));
                $balanceDollarQuery->whereMonth("created_at", date("m"))->whereYear("created_at", date("Y"));
            }

            $balance = $balanceQuery->first();
            $balanceDollar = $balanceDollarQuery->sum("value");
            $allBalance = $balance->balance ?? 0;
            $availableBalance = $balance ? $balance->balance - $balance->used : 0;

            // تأكد من أن البيانات صالحة للـ Chart
            $used = $balance->used ?? 0;
            $available = $availableBalance ?? 0;

            $chartData = [$used, $available];
            $usePercentage = ($allBalance > 0) ? (($used / $allBalance) * 100) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'allBalance' => $allBalance,
                    'availableBalance' => $available,
                    'balanceDollar' => $balanceDollar,
                    'used' => $used,
                    'usePercentage' => $usePercentage,
                    'chartData' => $chartData,
                    'showPaymentAlert' => (($used > 0) && ($usePercentage <= 90)) ? 1 : 0,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching balance data'
            ], 500);
        }
    }

    public function gameSummary(Request $request): JsonResponse
    {
        $countryID = $request->get('country_id');

        $game = CoinGameUserDailyAggregated::query()
            ->whereHas('user', function ($q) use ($countryID) {
                $q->when($countryID, fn($query, $countryID) => $query->where('country_id', $countryID));
            })
            ->selectRaw("
            SUM(total_played) as total_played,
            SUM(total_loss) as total_loss,
            SUM(total_win) as total_win,
            SUM(total_loss - total_win) as app_profit
        ")
            ->first();

        return response()->json([
            'total_played' => $game->total_played ?? 0,
            'total_loss' => $game->total_loss ?? 0,
            'total_win' => $game->total_win ?? 0,
            'app_profit' => $game->app_profit ?? 0,
        ]);
    }

    public function getStatsData(Request $request)
    {
        try {
            $countryID = $this->countryIds();

            // تجميع جميع الاستعلامات في مرة واحدة
            $userBaseQuery = User::when($countryID, fn($q) => $q->whereIn('country_id', $countryID));

            $stats = [
                'usersCount' => $userBaseQuery->count(),
                'newSignUpsToday' => $userBaseQuery->whereDate('created_at', today())->count(),
                'newSignUpsThisWeek' => $userBaseQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'newSignUpsThisMonth' => $userBaseQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'onlineUser' => $userBaseQuery->where('online', 1)->count(),
            ];

            // Peak Hours
            $peakHours = LiveTime::whereHas('user', function ($q) use ($countryID) {
                $q->when($countryID, fn($query) => $query->whereIn('country_id', $countryID));
            })
                ->selectRaw("FROM_UNIXTIME(start_time, '%H') as hour, COUNT(*) as total_sessions")
                ->whereRaw("DATE(FROM_UNIXTIME(start_time)) = CURDATE()")
                ->groupBy('hour')
                ->orderByDesc('total_sessions')
                ->first();

            if ($peakHours) {
                $time = Carbon::createFromTime($peakHours->hour);
                $time->locale(app()->getLocale());
                $stats['peakHour'] = $time->isoFormat('h A') . ' • ' . $peakHours->total_sessions . ' ' . __('Users');
            } else {
                $stats['peakHour'] = '0';
            }

            // Chat Statistics
            $stats['messagesToday'] = 0;
            $stats['messagesThisMonth'] = 0;
            $stats['usersWhoSend'] = 0;
            $stats['usersWhoNeverSend'] = $stats['usersCount'];
            $stats['openConversationsToday'] = 0;
            $stats['avgConversationDuration'] = 0;

            if (PackageHelper::isInstalled('chat')) {
                $chatMessageQuery = ChatMessage::when($countryID, function ($q) use ($countryID) {
                    $q->whereHas('user', fn($query) => $query->whereIn('country_id', $countryID));
                });

                $stats['messagesToday'] = (clone $chatMessageQuery)->whereDate('created_at', today())->count();
                $stats['messagesThisMonth'] = (clone $chatMessageQuery)->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count();

                $stats['usersWhoSend'] = (clone $chatMessageQuery)->distinct('user_id')->count('user_id');
                $stats['usersWhoNeverSend'] = $stats['usersCount'] - $stats['usersWhoSend'];

                $stats['openConversationsToday'] = (clone $chatMessageQuery)->whereDate('created_at', today())
                    ->distinct('chat_room_id')->count('chat_room_id');

                $stats['avgConversationDuration'] = ChatMessage::when($countryID, function ($q) use ($countryID) {
                    $q->whereHas('user', fn($query) => $query->whereIn('country_id', $countryID));
                })
                    ->selectRaw('chat_room_id, TIMESTAMPDIFF(MINUTE, MIN(created_at), MAX(created_at)) as duration')
                    ->groupBy('chat_room_id')
                    ->pluck('duration')
                    ->avg() ?? 0;
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics data'
            ], 500);
        }
    }

    public function financeCards(Request $request)
    {
        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->startOfDay();
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->startOfDay();

        $result = UserSallary::when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to))
            ->selectRaw('
                         SUM(pending_dollar) as total_dollars,
                         SUM(agency_sallary) as total_agency_dollars,
                         SUM(sallary) as total_user_dollars
                     ')
            ->first();

        $totalDollars = $result->total_dollars ?? 0;
        $totalAgencyDollars = $result->total_agency_dollars ?? 0;
        $totalUserDollars = $result->total_user_dollars ?? 0;

        $totalTargets = $totalDollars + $totalAgencyDollars + $totalUserDollars;

        $totalCharges = Charge::when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to))
            ->sum('usd');

        $totalPayments = CoinLog::when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to))
            ->sum('obtained_coins');

        $totalGiftsValue = GiftLog::when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn($q) => $q->where('created_at', '<=', $to))
            ->sum(\DB::raw('giftPrice * giftNum'));

        $rate = Common::getCoinsValue('user_coins');
        $totalGiftsUsd = $totalGiftsValue / $rate;

        return response()->json([
            'total_balance' => $totalTargets,
            'pending_balance' => $totalCharges,
            'available_balance' => $totalPayments,
            'today_balance' => $totalGiftsUsd
        ]);
    }

    public function financeTables(Request $request)
    {
        $payments = CoinLog::with('coin.paymentGateway')
            ->whereIn('status', [1, 2])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'gateway' => $p->coin->paymentGateway->title ?? '',
                'amount' => $p->obtained_coins,
                'status' => $p->status,
                'date' => \Carbon\Carbon::parse($p->created_at)->format('Y-m-d')
            ]);

        $withdrawals = collect();
        if (PackageHelper::isInstalled('usersWallet')) {
            $withdrawals = WalletLog::with('user.profile')->where('operation', 'subtract')
                ->latest()
                ->take(8)
                ->get()
                ->map(function ($w) {
                    $defaultImage = asset('images/businessman-icon.jpg');
                    $path = $w->user->profile?->avatar ?? null;
                    $url = $path ? getImagePath($path) : $defaultImage;

                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }

                    return [
                        'id' => $w->id,
                        'user_name' => $w->user->name ?? '',
                        'uuid' => $w->user->uuid ?? '',
                        'user_id' => $w->user_id,
                        'img' => $url,
                        'amount' => $w->amount,
                        'type' => $w->type,
                        'date' => $w->created_at->format('Y-m-d')
                    ];
                });
        }

        $topUsers = \DB::table('charges')
            ->select('user_id', \DB::raw('SUM(usd) as total_usd'), \DB::raw('MAX(created_at) as last_charge'))
            ->where('user_type', 'user')
            ->groupBy('user_id')
            ->orderByDesc('total_usd')
            ->limit(5)
            ->get();

        $users = $topUsers->map(function ($u) {
            $user = \App\Models\User::find($u->user_id);

            $defaultImage = asset('images/businessman-icon.jpg');
            $path = $user->profile?->avatar;

            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            return [
                'id' => $u->user_id,
                'name' => $user->name ?? 'غير معروف',
                'uuid' => $user->uuid ?? 'غير معروف',
                'avatar' => $url,
                'total_usd' => $u->total_usd,
                'last_charge' => $u->last_charge,
            ];
        });
        return response()->json([
            'payments' => $payments,
            'withdrawals' => $withdrawals,
            'topUsers' => $users
        ]);
    }

    public function financeChartIndex(Request $request)
    {
        $days = (int)$request->query('days', 7);

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : $to->copy()->subDays($days - 1)->startOfDay();

        $period = CarbonPeriod::create($from, $to);

        $values = array_fill_keys(
            array_map(fn($d) => $d->format('Y-m-d'), iterator_to_array($period)),
            0
        );

        $charges = Charge::selectRaw('DATE(created_at) as date, SUM(usd) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        foreach ($charges as $date => $total) {
            $values[$date] = (float)$total;
        }

        $labels = array_map(
            fn($d) => Carbon::parse($d)->format($days === 7 ? 'D' : 'd M'),
            array_keys($values)
        );

        return response()->json([
            'labels' => $labels,
            'values' => array_values($values),
        ]);
    }

    public function ajaxWalletLogs(Request $request)
    {

        if (!PackageHelper::isInstalled('usersWallet')) {
            return response()->json(['data' => []]);
        }

        $logs = WalletLog::with('user.profile')
            ->whereIn('operation', ['add', 'cut'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return response()->json([
            'data' => $logs->map(function ($log) {
                $defaultImage = asset('images/businessman-icon.jpg');
                $path = $log->user->profile?->avatar ?? null;
                $url = $path ? getImagePath($path) : $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                return [
                    'id' => $log->id,
                    'user_name' => $log->user ? $log->user->name : '-',
                    'user_id' => $log->user ? $log->user->id : '-',
                    'user_uuid' => $log->user ? $log->user->uuid : '-',
                    'img' => $url,
                    'amount' => $log->amount,
                    'operation' => $log->operation,
                    'type' => $log->type,
                    'before_amount' => $log->before_amount,
                    'after_amount' => $log->after_amount,
                    'created_at' => $log->created_at->format('Y-m-d H:i'),
                ];
            }),
        ]);
    }


    public function index2(Content $content)
    {
        $box = new Box(
            __('Coming Soon'),
            '<div style="text-align:center; padding:30px; font-size:20px;">🚧</div>'
        );

        return $content
            ->title(__('Home'))
            ->description(__('General Statistics'))
            ->row($box);
    }
}
