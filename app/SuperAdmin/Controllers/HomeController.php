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
        $peakHours = ChatMessage::whereHas('user', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
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
        $newRoomsToday = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->whereDate('created_at', today())
            ->count();

        $inactiveRooms = Room::whereHas('owner', fn($q) => $q->where('country_id', $countryID))
            ->whereDoesntHave('roomVisitors', function($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            })
            ->count();
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
            ->where('is_live', true)
            ->count();
        $audioRooms = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->where('type', 'audio')
            ->count();
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
        $roomsWithPk = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->has('lastPk')
            ->count();
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

            ->row(function (Row $row) use ($agencyCount, $usersCount, $bdCount, $onlineUser, $diAuth, $rooms, $agency_salaries, $user_salaries, $countryID, $peakHours, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration, $totalRooms, $newRoomsToday, $liveRooms, $audioRooms, $mostVisitedRoomCount, $avgVisitorsPerRoom, $roomsWithPk, $inactiveRooms, $longestActiveRoom, $avgMicPerRoom, $roomsWithMic, $percentageWithMic, $activeAgencies, $newAgenciesToday, $newAgenciesMonth, $topAgencies, $avgAgencyWallet, $totalMembers, $avgMembersPerAgency, $pendingJoins, $achievedTargets, $diamondsAchieved) {
                $row->column(12, new InfoBox(__('you Wallet'), 'money', 'green', '/', $diAuth . ' 💰'));

                $row->column(12, function ($column) use ($usersCount, $onlineUser, $countryID, $peakHours, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration) {
                    $column->row("<h3 style='margin:10px 0;'>👤 " . __('Users') . "</h3>");

                    $column->row(function (Row $row) use ($usersCount, $onlineUser, $peakHours, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration) {
                        $row->column(3, new InfoBox(__('Users Count'), 'users', 'aqua', 'superadmin/users', $usersCount));
                        $row->column(3, new InfoBox(__('Online Users Count'), 'user', 'blue', 'superadmin/users', $onlineUser));

                        $peakHourData = $peakHours->sortByDesc('total')->first();

                        if ($peakHourData) {
                            $time = Carbon::createFromTime($peakHourData->hour);
                            $time->locale(app()->getLocale());
                            $peakHour = $time->isoFormat('h A'); // مثال: 12 PM أو ١٢ م
                            $peakHourCount = $peakHourData->total;
                            $value = $peakHour . ' • ' . $peakHourCount . ' ' . __('Users');
                        } else {
                            $value = 'N/A';
                        }

                        $row->column(3, new InfoBox(__('Peak Hour'), 'clock-o', 'green', '', $value));

                        $row->column(3, new InfoBox(__('New Sign Ups Today'), 'user-plus', 'yellow', 'superadmin/users', $newSignUpsToday));
                        $row->column(3, new InfoBox(__('New Sign Ups This Week'), 'users', 'red', 'superadmin/users', $newSignUpsThisWeek));
                        $row->column(3, new InfoBox(__('New Sign Ups This Month'), 'user', 'purple', 'superadmin/users', $newSignUpsThisMonth));
                        $row->column(3, new InfoBox(__('Messages Today'), 'envelope', 'maroon', '', $messagesToday));
                        $row->column(3, new InfoBox(__('Messages This Month'), 'comments', 'teal', '', $messagesThisMonth));
                        $row->column(3, new InfoBox(__('Users Who Send Messages'), 'user', 'gray', '', $usersWhoSend));
                        $row->column(3, new InfoBox(__('Users Who Never Send'), 'user-times', 'orange', '', $usersWhoNeverSend));
                        $row->column(3, new InfoBox(__('Open Conversations Today'), 'comments-o', 'lime', '', $openConversationsToday));
                        $row->column(3, new InfoBox(__('Avg Conversation Duration (min)'), 'clock-o', 'olive', '', round($avgConversationDuration, 2)));
                    });

                    $column->row(function (Row $row) use ($countryID) {
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
                        $row->column(6, function ($column) use ($countryID) {
                            $topUsersByFollowers = User::withCount('followers')
                            ->where('country_id', $countryID)
                                ->orderByDesc('followers_count')
                                ->take(10)
                                ->get();

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
                    });
                });
                $row->column(12, function ($column) use ($rooms, $totalRoomsJoined, $totalRooms, $newRoomsToday, $liveRooms, $audioRooms, $mostVisitedRoomCount, $avgVisitorsPerRoom, $roomsWithPk, $inactiveRooms, $longestActiveRoom, $avgMicPerRoom, $roomsWithMic, $percentageWithMic) {
                    $column->row("<h3 style='margin:10px 0;'>🏠 " . __('Rooms') . "</h3>");

                    $column->row(function (Row $row) use ($rooms, $totalRoomsJoined, $totalRooms, $newRoomsToday, $liveRooms,
                        $audioRooms, $mostVisitedRoomCount, $avgVisitorsPerRoom, $roomsWithPk, $inactiveRooms, $longestActiveRoom, $avgMicPerRoom, $roomsWithMic, $percentageWithMic) {
                        $row->column(3, new InfoBox(__('Total Rooms'), 'building', 'aqua', 'superadmin/rooms', $totalRooms));
                        $row->column(3, new InfoBox(__('online rooms Count'), 'users', 'green', 'superadmin/rooms', $rooms));
                        $row->column(3, new InfoBox(__('Total Rooms Joined By Visitors'), 'building', 'yellow', 'superadmin/rooms', $totalRoomsJoined));
                        $row->column(3, new InfoBox(__('New Rooms Today'), 'plus', 'blue', 'superadmin/rooms', $newRoomsToday));
//                        $row->column(3, new InfoBox(__('Top Room Messages'), 'commenting', 'teal', 'superadmin/rooms/' . ($topRoom ? $topRoom->id : '#'), $topRoom ? $topRoom->messages_count : 0));
                        $row->column(3, new InfoBox(__('Live Rooms'), 'microphone', 'olive', 'superadmin/rooms', $liveRooms));
                        $row->column(3, new InfoBox(__('Audio Rooms'), 'music', 'orange', 'superadmin/rooms', $audioRooms));
                        $row->column(3, new InfoBox(__('Most Visited Room (visitors)'), 'users', 'lime', 'superadmin/rooms', $mostVisitedRoomCount));
                        $row->column(3, new InfoBox(__('Avg Visitors Per Room'), 'user-plus', 'gray', 'superadmin/rooms', round($avgVisitorsPerRoom, 2)));
                        $row->column(3, new InfoBox(__('Rooms With PK Battles'), 'gamepad', 'aqua', 'superadmin/rooms', $roomsWithPk));
                        $row->column(3, new InfoBox(__('Inactive Rooms (last 7 days)'), 'bed', 'green', 'superadmin/rooms', $inactiveRooms));
                        $row->column(3, new InfoBox(__('Longest Active Room (days)'), 'clock-o', 'yellow', 'superadmin/rooms', $longestActiveRoom));
                        $row->column(3, new InfoBox(__('Avg Mic Users per Room'), 'users', 'purple', 'superadmin/rooms', round($avgMicPerRoom, 2)));
                        $row->column(3, new InfoBox(__('Rooms With Mic Usage'), 'volume-up', 'maroon', 'superadmin/rooms', $roomsWithMic));
                        $row->column(3, new InfoBox(__('Rooms With Mic (%)'), 'pie-chart', 'teal', 'superadmin/rooms', round($percentageWithMic, 1) . '%'));
                    });
                });
                $row->column(12, function ($column) use ($agencyCount, $agency_salaries, $user_salaries, $activeAgencies, $newAgenciesToday, $newAgenciesMonth, $topAgencies, $avgAgencyWallet, $totalMembers, $avgMembersPerAgency, $pendingJoins, $achievedTargets, $diamondsAchieved) {
                    $column->row("<h3 style='margin:10px 0;'>🏢 " . __('Agencies') . "</h3>");

                    $column->row(function (Row $row) use ($agencyCount, $agency_salaries, $user_salaries, $activeAgencies, $newAgenciesToday, $newAgenciesMonth, $topAgencies, $avgAgencyWallet, $totalMembers, $avgMembersPerAgency, $pendingJoins, $achievedTargets, $diamondsAchieved) {
                        $row->column(3, new InfoBox(__('Agencies Count'), 'building', 'olive', 'superadmin/agencies', $agencyCount));
                        $row->column(3, new InfoBox(__('total agency salary'), 'building', 'lime', 'superadmin/agencies',  $agency_salaries));
                        $row->column(3, new InfoBox(__('Total Users Salary'), 'money', 'gray', 'superadmin/users', $user_salaries));
                        $row->column(3, new InfoBox(__('Active Agencies'), 'building', 'red', 'superadmin/agencies', $activeAgencies));
                        $row->column(3, new InfoBox(__('New Agencies Today'), 'plus', 'teal', 'superadmin/agencies', $newAgenciesToday));
                        $row->column(3, new InfoBox(__('New Agencies This Month'), 'calendar', 'orange', 'superadmin/agencies', $newAgenciesMonth));
                        $row->column(3, new InfoBox(__('Average Agency Wallet'), 'money', 'aqua', 'superadmin/agencies', round($avgAgencyWallet, 2)));
                        $row->column(3, new InfoBox(__('Total Members in Agencies'), 'users', 'maroon', 'superadmin/users', $totalMembers));
                        $row->column(3, new InfoBox(__('Avg Members Per Agency'), 'user', 'lime', 'superadmin/agencies', round($avgMembersPerAgency, 2)));
                        $row->column(3, new InfoBox(__('Pending Join Requests'), 'hourglass', 'purple', 'superadmin/agencies', $pendingJoins));
                        $row->column(3, new InfoBox(__('Agencies Achieved Targets'), 'flag', 'yellow', 'superadmin/agencies', $achievedTargets));
                        $row->column(3, new InfoBox(__('Diamonds Achieved by Hosts'), 'diamond', 'green', 'superadmin/users', $diamondsAchieved));
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
}
