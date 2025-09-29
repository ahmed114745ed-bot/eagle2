<?php

namespace App\SuperAdmin\Controllers;

use App\Admin\Widgets\Table;
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
use Encore\Admin\Widgets\Box;
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

//        $totalMessages = ChatMessage::count();
        $peakHours = ChatMessage::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $totalRoomsJoined = DB::table('room_visitors')->count();
        $activeRooms = Room::whereHas('roomVisitors', function ($q) {
            $q->with(['users' => fn($q) => $q->where('online', 1)]);
        })->count();
        $avgUsersPerRoom = Room::withCount('roomVisitors')->get()->avg('room_visitors_count');
        $topRooms = Room::withCount('messages')->orderByDesc('messages_count')->take(10)->get();

        //        $salaryData = \App\Models\BdSalary::where('bd_id', $appID)
        //            ->selectRaw('COALESCE(SUM(salary),0) AS total_sallary, COALESCE(SUM(cut_amount),0) AS total_cut')
        //            ->first();
        //        $finalSalary = truncateAndTrim($salaryData->total_sallary - $salaryData->total_cut, 2);
        //
        //        $finalWallet = '';
        return $content
            ->title(__('Home'))
            ->description('إحصائيات عامة')

            ->row(function (Row $row) use ($agencyCount, $usersCount, $bdCount, $onlineUser, $diAuth, $rooms, $agency_salaries, $user_salaries, $countryID, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined) {
                $row->column(12, new InfoBox(__('you Wallet'), 'money', 'green', '/', $diAuth . ' 💰'));

                $row->column(12, function ($column) use ($usersCount, $onlineUser, $user_salaries, $countryID, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined) {
                    $column->row("<h3 style='margin:10px 0;'>👤 " . __('Users') . "</h3>");

                    $column->row(function (Row $row) use ($usersCount, $onlineUser, $user_salaries, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined) {
                        $row->column(3, new InfoBox(__('Users Count'), 'users', 'aqua', 'superadmin/users', $usersCount));
                        $row->column(3, new InfoBox(__('Online Users Count'), 'user', 'green', 'superadmin/users', $onlineUser));
                        $row->column(3, new InfoBox(__('Total Users Salary'), 'money', 'yellow', 'superadmin/users', $user_salaries));

                        $peakHourData = $peakHours->sortByDesc('total')->first();
                        $peakHour = $peakHourData ? $peakHourData->hour . ':00' : 'N/A';
                        $peakHourCount = $peakHourData ? $peakHourData->total : 0;
                        $row->column(3, new InfoBox(__('Peak Hour'), 'clock-o', 'green', '', $peakHour . ' (' . $peakHourCount . ')'));

                    });

                    $column->row(function (Row $row) use ($countryID) {
                        // Right: chart view (Top Salaries)
                        $row->column(6, function ($column) use ($countryID) {
                            $topUsersByMessages = ChatMessage::selectRaw('user_id, COUNT(*) as total_messages')
                                ->groupBy('user_id')
                                ->orderByDesc('total_messages')
                                ->take(10)
                                ->get();

                            $labels = User::whereIn('id', $topUsersByMessages->pluck('user_id'))->pluck('name');
                            $data   = $topUsersByMessages->pluck('total_messages');

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
                $row->column(12, function ($column) use ($agencyCount, $agency_salaries) {
                    $column->row("<h3 style='margin:10px 0;'>🏢 " . __('Agencies') . "</h3>");

                    $column->row(function (Row $row) use ($agencyCount, $agency_salaries) {
                        $row->column(3, new InfoBox(__('Agencies Count'), 'building', 'aqua', 'superadmin/agencies', $agencyCount));
                        $row->column(3, new InfoBox(__('total agency salary'), 'building', 'aqua', 'superadmin/agencies',  $agency_salaries));
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
