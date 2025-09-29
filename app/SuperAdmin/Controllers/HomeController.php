<?php

namespace App\SuperAdmin\Controllers;

use App\Admin\Widgets\Table;
use App\Models\LiveTime;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use App\Models\Bd;
use App\Models\Room;
use App\Models\User;
use App\Models\Agency;
use App\Models\UserSallary;
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
        $newSignUpsToday = User::whereDate('created_at', today())
            ->where('country_id', $countryID)
            ->count();

        $newSignUpsThisWeek = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('country_id', $countryID)
            ->count();
        $newSignUpsThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('country_id', $countryID)
            ->count();
        $agencyCount = Agency::where('country_id', $countryID)->count();
        $bdCount = Bd::where('parent_id', auth()->id())->count();
        $onlineUser = User::where('country_id', $countryID)->where('online', 1)->count();
        $diAuth = Auth::user()->di;
        $rooms = Room::whereHas('owner.country', function ($q) use ($countryID) {
            $q->where('id',  $countryID);
        })->whereHas('roomVisitors')->count();
        $user_salaries   = UserSallary::query()->whereHas('user', function ($q) use ($countryID) {
            $q->where('agency_id', '!=', 0)->where('country_id', $countryID);
        })->sum(DB::raw('sallary - cut_amount'));
        $agency_salaries = AgencySallary::query()->whereHas('agency', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })->sum(DB::raw('sallary - cut_amount'));

        $peakHours = ChatMessage::whereHas('user', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $totalRoomsJoined = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->withCount('roomVisitors')
            ->get()
            ->sum('room_visitors_count');

        $activeRooms = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->whereHas('roomVisitors', function ($q) {
                $q->whereHas('user', function ($query) {
                    $query->where('online', 1);
                });
            })
            ->count();

        $avgUsersPerRoom = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->withCount('roomVisitors')
            ->get()
            ->avg('room_visitors_count');

        $topRooms = Room::whereHas('owner', function ($q) use ($countryID) {
            $q->where('country_id', $countryID);
        })
            ->withCount('messages')
            ->orderByDesc('messages_count')
            ->take(10)
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

        return $content
            ->title(__('Home'))
            ->description('إحصائيات عامة')

            ->row(function (Row $row) use ($agencyCount, $usersCount, $bdCount, $onlineUser, $diAuth, $rooms, $agency_salaries, $user_salaries, $countryID, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration) {
                $row->column(12, new InfoBox(__('you Wallet'), 'money', 'green', '/', $diAuth . ' 💰'));

                $row->column(12, function ($column) use ($usersCount, $onlineUser, $countryID, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration) {
                    $column->row("<h3 style='margin:10px 0;'>👤 " . __('Users') . "</h3>");

                    $column->row(function (Row $row) use ($usersCount, $onlineUser, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined, $newSignUpsToday, $newSignUpsThisWeek, $newSignUpsThisMonth, $messagesToday, $messagesThisMonth, $usersWhoSend, $usersWhoNeverSend, $openConversationsToday, $avgConversationDuration) {
                        $row->column(3, new InfoBox(__('Users Count'), 'users', 'aqua', 'superadmin/users', $usersCount));
                        $row->column(3, new InfoBox(__('Online Users Count'), 'user', 'blue', 'superadmin/users', $onlineUser));

                        $peakHourData = $peakHours->sortByDesc('total')->first();
                        $peakHour = $peakHourData ? $peakHourData->hour . ':00' : 'N/A';
                        $peakHourCount = $peakHourData ? $peakHourData->total : 0;
                        $row->column(3, new InfoBox(__('Peak Hour'), 'clock-o', 'green', '', $peakHour . ' (' . $peakHourCount . ')'));
                        $row->column(3, new InfoBox(__('New Sign Ups Today'), 'user-plus', 'yellow', 'superadmin/users', $newSignUpsToday));
                        $row->column(3, new InfoBox(__('New Sign Ups This Week'), 'users', 'red', 'superadmin/users', $newSignUpsThisWeek));
                        $row->column(3, new InfoBox(__('New Sign Ups This Month'), 'user', 'purple', 'superadmin/users', $newSignUpsThisMonth));
                        $row->column(3, new InfoBox(__('Messages Today'), 'envelope', 'maroon', '', $messagesToday));
                        $row->column(3, new InfoBox(__('Messages This Month'), 'comments', 'teal', '', $messagesThisMonth));
                        $row->column(3, new InfoBox(__('Users Who Send Messages'), 'user', 'navy', '', $usersWhoSend));
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

                    });
                });
                $row->column(12, function ($column) use ($rooms, $totalRoomsJoined, $activeRooms, $avgUsersPerRoom, $topRooms) {
                    $column->row("<h3 style='margin:10px 0;'>🏠 " . __('Rooms') . "</h3>");

                    $column->row(function (Row $row) use ($rooms, $totalRoomsJoined, $activeRooms, $avgUsersPerRoom, $topRooms) {
                        $row->column(3, new InfoBox(__('online rooms Count'), 'users', 'aqua', 'superadmin/rooms', $rooms));
                        $row->column(3, new InfoBox(__('Total Rooms Joined By Visitors'), 'building', 'green', 'superadmin/rooms', $totalRoomsJoined));
                        $row->column(3, new InfoBox(__('Active Rooms'), 'users', 'yellow', 'superadmin/rooms', $activeRooms));
                        $row->column(3, new InfoBox(__('Avg Users Per Room'), 'user-plus', 'purple', 'superadmin/rooms', round($avgUsersPerRoom, 2)));

                        $topRoom = $topRooms->first();
                        $row->column(3, new InfoBox(__('Top Room Messages'), 'commenting', 'red', 'superadmin/rooms/' . ($topRoom ? $topRoom->id : '#'), $topRoom ? $topRoom->messages_count : 0));
                    });
                });
                $row->column(12, function ($column) use ($agencyCount, $agency_salaries, $user_salaries) {
                    $column->row("<h3 style='margin:10px 0;'>🏢 " . __('Agencies') . "</h3>");

                    $column->row(function (Row $row) use ($agencyCount, $agency_salaries, $user_salaries) {
                        $row->column(3, new InfoBox(__('Agencies Count'), 'building', 'aqua', 'superadmin/agencies', $agencyCount));
                        $row->column(3, new InfoBox(__('total agency salary'), 'building', 'aqua', 'superadmin/agencies',  $agency_salaries));
                        $row->column(3, new InfoBox(__('Total Users Salary'), 'money', 'yellow', 'superadmin/users', $user_salaries));
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
