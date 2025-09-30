<?php

namespace App\SuperAdmin\Controllers;

use App\Models\AgencyJoinRequest;
use App\Models\LiveTime;
use App\Models\UserTarget;
use App\Models\Bd;
use App\Models\Room;
use App\Models\User;
use App\Models\Agency;
use App\Models\UserSallary;
use Carbon\Carbon;
use Encore\Admin\Layout\Row;
use App\Models\AgencySallary;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Chat\Entities\ChatMessage;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index(Content $content)
    {
        $countryID = Auth::user()->country_id;
        $usersCount = User::where('country_id', $countryID)->count();

        //users
        $newSignUpsToday = User::whereDate('created_at', today())->where('country_id', $countryID)->count();
        $newSignUpsThisWeek = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('country_id', $countryID)->count();
        $newSignUpsThisMonth = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('country_id', $countryID)->count();
        $onlineUser = User::where('country_id', $countryID)->where('online', 1)->count();
        $topUsersByFollowers = User::withCount('followers')
            ->with('packs','profile')
            ->where('country_id', $countryID)
            ->orderByDesc('followers_count')
            ->take(10)
            ->get();
        $peakHours = LiveTime::
                whereHas('room.owner', function ($q) use ($countryID) {
                    $q->where('country_id', $countryID);
                })
                ->selectRaw("FROM_UNIXTIME(start_time, '%H') as hour, COUNT(*) as total_sessions, SUM(hours) as total_duration")
                ->whereRaw("DATE(FROM_UNIXTIME(start_time)) = CURDATE()")
                ->groupBy('hour')
                ->orderByDesc('total_sessions')
                ->limit(1)
                ->first();
        $messagesToday = ChatMessage::whereHas('user', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->whereDate('created_at', today())
            ->count();
        $messagesThisMonth = ChatMessage::whereHas('user', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $usersWhoSend = ChatMessage::whereHas('user', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->distinct('user_id')
            ->count('user_id');
        $totalUsers = User::where('country_id', $countryID)->count();
        $usersWhoNeverSend = $totalUsers - $usersWhoSend;
        $openConversationsToday = ChatMessage::whereHas('user', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->whereDate('created_at', today())
            ->distinct('chat_room_id')
            ->count('chat_room_id');
        $avgConversationDuration = ChatMessage::whereHas('user', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->selectRaw('chat_room_id, TIMESTAMPDIFF(MINUTE, MIN(created_at), MAX(created_at)) as duration')
            ->groupBy('chat_room_id')
            ->pluck('duration')
            ->avg() ?? 0;

        //rooms
        $rooms = Room::whereHas('owner.country', function ($q) use ($countryID) {
            $q->where('id',  $countryID);
        })->whereHas('roomVisitors')->count();
        $totalRoomsJoined = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->withCount('roomVisitors')
            ->get()
            ->sum('room_visitors_count');
        $totalRooms = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })->count();
        $longestActiveRoom = Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
            ->with(['roomVisitors' => function ($q) {
                $q->select('id', 'room_id', 'created_at');
            }])
            ->get()
            ->map(function ($room) {
                $min = $room->roomVisitors->min('created_at');
                $max = $room->roomVisitors->max('created_at');

                if (!$min || !$max) {
                    return 0;
                }

                return Carbon::parse($max)->diffInDays(Carbon::parse($min));
            })
            ->max() ?? 0;
        $liveRooms = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->where('type', 'live')
            ->selectRaw('is_live, COUNT(*) as total')
            ->groupBy('is_live')
            ->pluck('total','is_live');

        $liveRoomsTrue = $liveRooms[1] ?? 0;
        $liveRoomsFalse = $liveRooms[0] ?? 0;
        $mostVisitedRoom = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->withCount('roomVisitors')
            ->orderByDesc('room_visitors_count')
            ->first();
        $mostVisitedRoomCount = $mostVisitedRoom?->room_visitors_count ?? 0;
        $avgVisitorsPerRoom = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->withCount('roomVisitors')
            ->get()
            ->avg('room_visitors_count');

        $avgMicPerRoom = Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
            ->pluck('microphone')
            ->filter()
            ->map(fn($mics) => count(array_filter(explode(',', $mics))))
            ->avg();
        //return back another way
//        $topMicRooms = Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
//            ->get()
//            ->map(function ($room) {
//                $micCount = count(array_filter(explode(',', $room->microphone ?? '')));
//                return [
//                    'room' => $room,
//                    'mic_count' => $micCount
//                ];
//            })
//            ->sortByDesc('mic_count')
//            ->take(10);
        $roomsWithMic = Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
            ->whereNotNull('microphone')
            ->where('microphone', '!=', '')
            ->count();
        $percentageWithMic = $totalRooms > 0 ? ($roomsWithMic / $totalRooms) * 100 : 0;

        //agencies
        $agencyCount = Agency::where('country_id', $countryID)->count();
        $user_salaries   = UserSallary::query()->whereHas('user', function ($q) use ($countryID) {
            $q->where('agency_id', '!=', 0)->where('country_id', $countryID);
        })->sum(DB::raw('sallary - cut_amount'));
        $agency_salaries = AgencySallary::query()->whereHas('agency', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })->sum(DB::raw('sallary - cut_amount'));

        $activeAgencies = Agency::where('country_id', $countryID)
            ->whereHas('agencySalaries', fn($q) => $q->whereMonth('created_at', now()->month))
            ->count();
        $newAgenciesToday = Agency::where('country_id', $countryID)->whereDate('created_at', today())->count();
        $newAgenciesMonth = Agency::where('country_id', $countryID)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $topAgencies = Agency::where('country_id', $countryID)
            ->orderByDesc('coins')
            ->take(10)
            ->get(['id','name','coins']);
        $avgAgencyWallet = Agency::where('country_id',$countryID)->avg('coins');
        $totalMembers = User::where('country_id',$countryID)->where('agency_id','!=',0)->count();
        $avgMembersPerAgency = $agencyCount > 0 ? $totalMembers / $agencyCount : 0;
        $pendingJoins = AgencyJoinRequest::whereHas('agency', fn($q) => $q->where('country_id',$countryID))
            ->where('status',0)->count();
        $achievedTargets = UserTarget::whereHas('agency', fn($q) => $q->where('country_id',$countryID))
            ->where('add_month', now()->month)
            ->where('add_year', now()->year)
            ->where('agency_obtain','>',0)
            ->count();
        $diamondsAchieved = UserSallary::whereHas('user', fn($q) => $q->where('country_id',$countryID))
            ->sum('achieved_diamond');

        //others
        $bdCount = Bd::where('parent_id', auth()->id())->count();
        $diAuth = Auth::user()->di;

        return $content
            ->title(__('Home'))
            ->description('إحصائيات عامة')

            ->row(function (Row $row) use ($agencyCount, $usersCount, $bdCount, $onlineUser, $diAuth, $rooms, $agency_salaries, $user_salaries, $countryID, $peakHours, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration, $totalRooms, $liveRooms, $mostVisitedRoomCount, $avgVisitorsPerRoom, $longestActiveRoom, $avgMicPerRoom, $roomsWithMic, $percentageWithMic, $activeAgencies, $newAgenciesToday, $newAgenciesMonth, $topAgencies, $avgAgencyWallet, $totalMembers, $avgMembersPerAgency, $pendingJoins, $achievedTargets, $diamondsAchieved, $liveRoomsTrue, $liveRoomsFalse,$topUsersByFollowers) {
                $row->column(12, new InfoBox(__('you Wallet'), 'money', 'green', '/', $diAuth . '💎'));
                $row->column(12, function ($column) use ($usersCount, $onlineUser, $countryID, $peakHours, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration,$topUsersByFollowers) {
                    $column->row("<h3 style='margin:10px 0;'>👤 " . __('Users') . "</h3>");

                    $column->row(function (Row $row) use ($usersCount, $onlineUser, $peakHours, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration) {
                        $row->column(3, new InfoBox(__('Users Count'), 'users', 'aqua', 'superadmin/users', $usersCount));
                        $row->column(3, new InfoBox(__('Online Users Count'), 'user', 'blue', 'superadmin/users', $onlineUser));
                        if ($peakHours) {
                            $time = Carbon::createFromTime($peakHours->hour);
                            $time->locale(app()->getLocale());
                            $peakHour = $time->isoFormat('h A');
                            $peakHourCount = $peakHours->total_sessions;
                            $value = $peakHour . ' • ' . $peakHourCount . ' ' . __('Users');
                        } else {
                            $value = 'N/A';
                        }
                        $row->column(3, new InfoBox(__('Peak Hour'), 'clock-o', 'green', 'superadmin/users', $value));
                        $row->column(3, new InfoBox(__('New Sign Ups Today'), 'user-plus', 'yellow', 'superadmin/users', $newSignUpsToday));
                        $row->column(3, new InfoBox(__('New Sign Ups This Week'), 'users', 'red', 'superadmin/users', $newSignUpsThisWeek));
                        $row->column(3, new InfoBox(__('New Sign Ups This Month'), 'user', 'purple', 'superadmin/users', $newSignUpsThisMonth));
                        $row->column(3, new InfoBox(__('Messages Today'), 'envelope', 'maroon', 'superadmin/users', $messagesToday));
                        $row->column(3, new InfoBox(__('Messages This Month'), 'comments', 'teal', 'superadmin/users', $messagesThisMonth));
                        $row->column(3, new InfoBox(__('Users Who Send Messages'), 'user', 'gray', 'superadmin/users', $usersWhoSend));
                        $row->column(3, new InfoBox(__('Users Who Never Send'), 'user-times', 'orange', 'superadmin/users', $usersWhoNeverSend));
                        $row->column(3, new InfoBox(__('Open Conversations Today'), 'comments-o', 'lime', 'superadmin/users', $openConversationsToday));
                        $row->column(3, new InfoBox(__('Avg Conversation Duration (min)'), 'clock-o', 'olive', 'superadmin/users', round($avgConversationDuration, 2)));
                    });

                    $column->row(function (Row $row) use ($countryID,$topUsersByFollowers) {
                        // Right: chart view (Top Salaries)
                        $row->column(6, function ($column) {
                            $topUsersByLiveTime = LiveTime::query()
                                ->selectRaw('uid, SUM(hours) as total_hours, COUNT(DISTINCT DATE(created_at)) as active_days')
                                ->groupBy('uid')
                                ->havingRaw('SUM(hours) >= 1')
                                ->orderByDesc('total_hours')
                                ->take(10)
                                ->get();

                            $labels = User::whereIn('id', $topUsersByLiveTime->pluck('uid'))->pluck('name');
                            $data   = $topUsersByLiveTime->pluck('total_hours');

                            $view = view('admin.widgets.users_chart', [
                                'labels' => $labels,
                                'data'   => $data,
                            ])->render();

                            $column->row($view);
                        });

                        // Left: top 10 salaries
                        $row->column(6, function ($column) use ($countryID,$topUsersByFollowers) {
                        

                            $labels = $topUsersByFollowers->pluck('name');
                            $data   = $topUsersByFollowers->pluck('followers_count');

                            $view = view('admin.widgets.top_followers_chart', [
                                'labels' => $labels,
                                'data'   => $data,
                            ])->render();

                            $column->row($view);
                        });

                        $row->column(6, function ($column) use ($countryID) {
                            $currMonth = now()->month;
                            $prevMonth = now()->subMonth()->month;

                            $signups = User::where('country_id', $countryID)
                                ->selectRaw("
                                YEAR(created_at) as year,
                                MONTH(created_at) as month,
                                FLOOR((DAY(created_at)-1)/7)+1 as week_of_month,
                                COUNT(*) as total
                            ")
                                ->whereIn(DB::raw('MONTH(created_at)'), [$currMonth, $prevMonth])
                                ->groupBy('year','month','week_of_month')
                                ->orderBy('year')
                                ->orderBy('month')
                                ->orderBy('week_of_month')
                                ->get();

                            $labels = ['Week 1','Week 2','Week 3','Week 4'];

                            $dataCurrent = [];
                            $dataPrevious = [];

                            foreach (range(1,4) as $week) {
                                $dataCurrent[]  = $signups->where('month',$currMonth)->where('week_of_month',$week)->sum('total');
                                $dataPrevious[] = $signups->where('month',$prevMonth)->where('week_of_month',$week)->sum('total');
                            }

                            $view = view('admin.widgets.signups_weekly_chart', [
                                'labels'       => $labels,
                                'dataCurrent'  => $dataCurrent,
                                'dataPrevious' => $dataPrevious,
                            ])->render();

                            $column->row($view);
                        });

                        $row->column(6, function ($column) {
                            $view = view('admin.widgets.peak_hours_card')->render();
                            $column->row($view);
                        });
                    });
                });

                $row->column(12, function ($column) use ($countryID,$topUsersByFollowers) {
                    $column->row(function (Row $row) use ($countryID ,$topUsersByFollowers) {
                        $row->column(6, function ($col) use ($topUsersByFollowers) {
                            $top5 = $topUsersByFollowers->take(5);
                
                            $view5 = view('admin.widgets.top_followers_table', [
                                'top5' => $top5,
                            ])->render();
                        
                            $col->row($view5);
                        });

                        $row->column(6, function ($col) use ($countryID) {
                            $view = view('admin.widgets.users_online_chart')->render();
                            $col->row($view);
                        });
                    });
                });
                $row->column(12, function ($column) use ($countryID, $rooms, $totalRoomsJoined, $totalRooms, $liveRooms, $mostVisitedRoomCount, $avgVisitorsPerRoom, $longestActiveRoom, $avgMicPerRoom, $roomsWithMic, $percentageWithMic, $liveRoomsTrue, $liveRoomsFalse,) {
                    $column->row("<h3 style='margin:10px 0;'>🏠 " . __('Rooms') . "</h3>");

                    $column->row(function (Row $row) use ($rooms, $totalRoomsJoined, $totalRooms, $liveRoomsTrue, $liveRoomsFalse, $mostVisitedRoomCount, $avgVisitorsPerRoom, $longestActiveRoom, $avgMicPerRoom, $roomsWithMic, $percentageWithMic) {
                        $row->column(3, new InfoBox(__('Total Rooms'), 'building', 'aqua', 'superadmin/rooms', $totalRooms));
                        $row->column(3, new InfoBox(__('online rooms Count'), 'users', 'green', 'superadmin/rooms', $rooms));
                        $row->column(3, new InfoBox(__('Total Rooms Joined By Visitors'), 'building', 'yellow', 'superadmin/rooms', $totalRoomsJoined));
//                        $row->column(3, new InfoBox(__('Top Room Messages'), 'commenting', 'teal', 'superadmin/rooms/' . ($topRoom ? $topRoom->id : '#'), $topRoom ? $topRoom->messages_count : 0));
                        $row->column(3, new InfoBox(__('Live Rooms (Active)'), 'microphone', 'green', 'superadmin/live-rooms', $liveRoomsTrue));
                        $row->column(3, new InfoBox(__('Live Rooms (Inactive)'), 'microphone-slash', 'red', 'superadmin/live-rooms', $liveRoomsFalse));
                        $row->column(3, new InfoBox(__('Most Visited Room (visitors)'), 'users', 'lime', 'superadmin/rooms', $mostVisitedRoomCount));
                        $row->column(3, new InfoBox(__('Avg Visitors Per Room'), 'user-plus', 'gray', 'superadmin/rooms', round($avgVisitorsPerRoom, 2)));
                        $row->column(3, new InfoBox(__('Longest Active Room (days)'), 'clock-o', 'yellow', 'superadmin/rooms', $longestActiveRoom));
                        $row->column(3, new InfoBox(__('Avg Mic Users per Room'), 'users', 'purple', 'superadmin/rooms', round($avgMicPerRoom, 2)));
                        $row->column(3, new InfoBox(__('Rooms With Mic Usage'), 'volume-up', 'maroon', 'superadmin/rooms', $roomsWithMic));
                        $row->column(3, new InfoBox(__('Rooms With Mic (%)'), 'pie-chart', 'teal', 'superadmin/rooms', round($percentageWithMic, 1) . '%'));
                    });

                    $column->row(function (Row $row) use ($countryID) {
                        //chart 1
                        $row->column(6, function ($column) use ($countryID) {
                            $roomsWithPk = Room::whereHas('owner', function ($q) use ($countryID) {
                                $q->where('country_id', $countryID);
                            })
                                ->has('lastPk')
                                ->count();

                            $audioRooms = Room::whereHas('owner', function ($q) use ($countryID) {
                                $q->where('country_id', $countryID);
                            })
                                ->where('type', 'audio')
                                ->count();

                            $liveRooms = Room::whereHas('owner', function ($q) use ($countryID) {
                                $q->where('country_id', $countryID);
                            })
                                ->where('type', 'live')
                                ->count();

                            $inactiveRooms = Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
                                ->whereDoesntHave('roomVisitors')
                                ->count();

                            $roomStats = [
                                __('Rooms with PK')  => $roomsWithPk,
                                __('Audio Rooms')    => $audioRooms,
                                __('Live Rooms')     => $liveRooms,
                                __('Inactive Rooms') => $inactiveRooms,
                            ];

                            $view = view('admin.widgets.rooms_distribution_chart', [
                                'labels' => array_keys($roomStats),
                                'data'   => array_values($roomStats),
                            ])->render();

                            $column->row($view);
                        });

                        //chart2
                        $row->column(6, function ($column) {
                            $view = view('admin.widgets.rooms_activity_chart')->render();
                            $column->row($view);
                        });
                    });

                });
                $row->column(12, function ($column) use ($agencyCount, $agency_salaries, $user_salaries, $activeAgencies, $newAgenciesToday, $newAgenciesMonth, $topAgencies, $avgAgencyWallet, $totalMembers, $avgMembersPerAgency, $pendingJoins, $achievedTargets, $diamondsAchieved) {
                    $column->row("<h3 style='margin:10px 0;'>🏢 " . __('Agencies') . "</h3>");

                    $column->row(function (Row $row) use ($agencyCount, $agency_salaries, $user_salaries, $activeAgencies, $newAgenciesToday, $newAgenciesMonth, $topAgencies, $avgAgencyWallet, $totalMembers, $avgMembersPerAgency, $pendingJoins, $achievedTargets, $diamondsAchieved) {
                        $row->column(3, new InfoBox(__('Agencies Count'), 'building', 'olive', 'superadmin/agencies', $agencyCount));
                        $row->column(3, new InfoBox(__('total agency salary'), 'building', 'lime', 'superadmin/agencies',  $agency_salaries));
                        $row->column(3, new InfoBox(__('Total Users Salary'), 'money', 'gray', 'superadmin/ag/users', $user_salaries));
                        $row->column(3, new InfoBox(__('Active Agencies'), 'building', 'red', 'superadmin/agencies', $activeAgencies));
                        $row->column(3, new InfoBox(__('New Agencies Today'), 'plus', 'teal', 'superadmin/agencies', $newAgenciesToday));
                        $row->column(3, new InfoBox(__('New Agencies This Month'), 'calendar', 'orange', 'superadmin/agencies', $newAgenciesMonth));
                        $row->column(3, new InfoBox(__('Average Agency Wallet'), 'money', 'aqua', 'superadmin/agencies', round($avgAgencyWallet, 2)));
                        $row->column(3, new InfoBox(__('Total Members in Agencies'), 'users', 'maroon', 'superadmin/ag/users', $totalMembers));
                        $row->column(3, new InfoBox(__('Avg Members Per Agency'), 'user', 'lime', 'superadmin/agencies', round($avgMembersPerAgency, 2)));
                        $row->column(3, new InfoBox(__('Pending Join Requests'), 'hourglass', 'purple', 'superadmin/agencies', $pendingJoins));
                        $row->column(3, new InfoBox(__('Agencies Achieved Targets'), 'flag', 'yellow', 'superadmin/agencies', $achievedTargets));
                        $row->column(3, new InfoBox(__('Diamonds Achieved by Hosts'), 'diamond', 'green', 'superadmin/ag/users', $diamondsAchieved));
                    });
                });
                $row->column(12, function ($column) use ($bdCount) {
                    $column->row("<h3 style='margin:10px 0;'>💼 " . __('BD') . "</h3>");

                    $column->row(function (Row $row) use ($bdCount) {
                        $row->column(3, new InfoBox(__('Bd Count'), 'briefcase', 'aqua', 'superadmin/usersBD', $bdCount));
                    });
                });
            });
    }


    public function peakHours(Request $request)
    {
        $countryID = Auth::user()->country_id;
        $period = $request->get('period', 'day');
    
        $query = DB::table('live_times')
            ->join('rooms', 'live_times.uid', '=', 'rooms.id')
            ->join('users', 'rooms.uid', '=', 'users.id')
            ->where('users.country_id', $countryID);
    
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
            'data'   => $rows->pluck('total'),
        ]);
    }

    public function onlineStats()
    {
       
        $countryID = Auth::user()->country_id;
        $online  = User::where('country_id', $countryID)->where('isOnline', 1)->count();
        $offline = User::where('country_id', $countryID)->where('isOnline', 0)->count();

    
        return response()->json(data: [
            'online'  => $online,
            'offline' => $offline,
        ]);
    }
    public function roomsActivity(Request $request)
    {
        $period = $request->get('period', 'day');
        $countryID = Auth::user()->country_id;

        if ($period === 'day') {
            $dates = collect(range(0, 6))
                ->map(fn($i) => now()->subDays($i)->format('Y-m-d'))
                ->reverse()
                ->values();

            $newRooms = $dates->map(fn($date) =>
            Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
                ->whereDate('created_at', $date)
                ->count()
            )->values();

            $inactiveRooms = $dates->map(fn($date) =>
            Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
                ->whereDoesntHave('roomVisitors', function($q) use ($date) {
                    $q->where('created_at', '>=', $date);
                })
                ->count()
            )->values();

            $labels = $dates;

        } elseif ($period === 'week') {
            // last 4 weeks
            $weeks = collect(range(0, 3))
                ->map(fn($i) => now()->subWeeks($i)->format('o-\WW'))
                ->reverse()
                ->values();

            $newRooms = $weeks->map(function ($week) use ($countryID) {
                [$year, $weekNum] = explode('-W', $week);
                return Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
                    ->whereYear('created_at', $year)
                    ->whereRaw("WEEK(created_at, 1) = ?", [$weekNum])
                    ->count();
            })->values();

            $inactiveRooms = $weeks->map(function ($week) use ($countryID) {
                [$year, $weekNum] = explode('-W', $week);
                return Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
                    ->whereDoesntHave('roomVisitors', function($q) use ($year, $weekNum) {
                        $q->whereYear('created_at', $year)
                            ->whereRaw("WEEK(created_at, 1) = ?", [$weekNum]);
                    })
                    ->count();
            })->values();

            $labels = $weeks;

        } elseif ($period === 'month') {
            // last 6 months
            $months = collect(range(0, 5))
                ->map(fn($i) => now()->subMonths($i)->format('Y-m'))
                ->reverse()
                ->values();

            $newRooms = $months->map(function ($month) use ($countryID) {
                [$year, $monthNum] = explode('-', $month);
                return Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $monthNum)
                    ->count();
            })->values();

            $inactiveRooms = $months->map(function ($month) use ($countryID) {
                [$year, $monthNum] = explode('-', $month);
                return Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
                    ->whereDoesntHave('roomVisitors', function($q) use ($year, $monthNum) {
                        $q->whereYear('created_at', $year)
                            ->whereMonth('created_at', $monthNum);
                    })
                    ->count();
            })->values();

            $labels = $months;
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'newRooms' => $newRooms,
            'inactiveRooms' => $inactiveRooms,
        ]);
    }
}
