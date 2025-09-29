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

        $totalMessages = ChatMessage::count();
        $peakHours = ChatMessage::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $totalRoomsJoined = DB::table('room_visitors')
        ->count();
        $activeRooms = Room::whereHas('roomVisitors', function ($q) {
            $q->with(['users' => fn($q) => $q->where('online', 1)]);
        })->count();
        $avgUsersPerRoom = Room::withCount('roomVisitors')
            ->get()
            ->avg('room_visitors_count');
        $topRooms = Room::withCount('messages')
            ->orderByDesc('messages_count')
            ->take(10)
            ->get();

        //        $salaryData = \App\Models\BdSalary::where('bd_id', $appID)
        //            ->selectRaw('COALESCE(SUM(salary),0) AS total_sallary, COALESCE(SUM(cut_amount),0) AS total_cut')
        //            ->first();
        //        $finalSalary = truncateAndTrim($salaryData->total_sallary - $salaryData->total_cut, 2);
        //
        //        $finalWallet = '';
        return $content
            ->title(__('Home'))
            ->description('إحصائيات عامة')

            ->row(function (Row $row) use ($agencyCount, $usersCount, $bdCount, $onlineUser, $diAuth, $rooms, $agency_salaries, $user_salaries, $countryID, $totalMessages, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined) {
                $row->column(12, function ($column) use ($usersCount, $onlineUser, $user_salaries, $countryID, $totalMessages, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined) {
                    $column->row("<h3 style='margin:10px 0;'>👤 " . __('Users') . "</h3>");

                    $column->row(function (Row $row) use ($usersCount, $onlineUser, $user_salaries, $totalMessages, $peakHours, $topRooms, $avgUsersPerRoom, $activeRooms, $totalRoomsJoined) {
                        $row->column(4, new InfoBox(__('Users Count'), 'users', 'aqua', 'superadmin/users', $usersCount));
                        $row->column(4, new InfoBox(__('Online Users Count'), 'user', 'green', 'superadmin/users', $onlineUser));
                        $row->column(4, new InfoBox(__('Total Users Salary'), 'money', 'yellow', 'superadmin/users', $user_salaries));

                        // Chat cards
                        $row->column(4, new InfoBox(__('Total Messages'), 'message', 'purple', 'superadmin/chats', $totalMessages));

                        $row->column(3, new InfoBox(__('Total Rooms Joined By Visitors'), 'building', 'aqua', 'superadmin/rooms', $totalRoomsJoined));
                        $row->column(3, new InfoBox(__('Active Rooms'), 'users', 'green', 'superadmin/rooms', $activeRooms));
                        $row->column(3, new InfoBox(__('Avg Users Per Room'), 'user-friends', 'yellow', 'superadmin/rooms', round($avgUsersPerRoom, 2)));

                        $topRoom = $topRooms->first();
                        $row->column(3, new InfoBox(__('Top Room Messages'), 'comments', 'purple', 'superadmin/rooms/' . ($topRooms ? $topRoom->id : '#'), $topRooms ? $topRoom->messages_count : 0));

                        $peakHourData = $peakHours->sortByDesc('total')->first();
                        $peakHour = $peakHourData ? $peakHourData->hour . ':00' : 'N/A';
                        $peakHourCount = $peakHourData ? $peakHourData->total : 0;
                        $row->column(3, new InfoBox(__('Peak Hour'), 'clock', 'green', 'superadmin/chats', $peakHour . ' (' . $peakHourCount . ')'));

                    });

                    $column->row(function (Row $row) use ($countryID) {
                        // Right: chart view (Top Salaries)
                        $row->column(6, function ($column) use ($countryID) {
                            $topUsers = UserSallary::with('user:id,name')
                                ->whereHas('user', function ($q) use ($countryID) {
                                    $q->where('country_id', $countryID);
                                })
                                ->selectRaw('user_id, SUM(sallary - cut_amount) as net_salary')
                                ->groupBy('user_id')
                                ->orderByDesc('net_salary')
                                ->take(10)
                                ->get();

                            $labels = $topUsers->pluck('user.name');
                            $data   = $topUsers->pluck('net_salary');

                            $view = view('admin.widgets.users_chart', [
                                'labels' => $labels,
                                'data'   => $data,
                            ])->render();

                            $column->row($view);
                        });

                        // Left: top 10 salaries
                        $row->column(6, function ($column) use ($countryID) {
                            $grid = Admin::grid(UserSallary::class, function (Grid $grid) use ($countryID) {
                                $grid->model()
                                    ->with('user.packs')
                                    ->whereHas('user', function ($q) use ($countryID) {
                                        $q->where('country_id', $countryID);
                                    })
                                    ->selectRaw('user_id, SUM(sallary - cut_amount) as net_salary')
                                    ->groupBy('user_id')
                                    ->orderByDesc('net_salary')
                                    ->take(10);

                                $grid->column('user.id', __('User ID'));
                                $grid->column('user.name', __('Name'))
                                    ->display(function ($name) {
                                        $url = url("admin/users/{$this->id}");
                                        return "<a href='{$url}' target='_blank'>{$name}</a>";
                                    });
                                $grid->column('net_salary', __('Net Salary'))->display(function ($value) {
                                    return number_format($value);
                                });

                                $grid->disableActions();
                                $grid->disableCreateButton();
                                $grid->disableExport();
                                $grid->disableRowSelector();
                                $grid->disablePagination();
                                $grid->disableFilter();
                                $grid->disableColumnSelector();
                                $grid->tools(function ($tools) {
                                    $tools->append("<h3 style='margin:0;'>" . __('Top Salaries') . "</h3>");
                                });

                                Admin::style('.grid-table thead { display: none; }');
                            });

                            $column->row($grid);
                        });

                    });
                });
                $row->column(6, new InfoBox(__('Agencies Count'), 'building', 'aqua', 'superadmin/agencies', $agencyCount));
                $row->column(6, new InfoBox(__('total agency salary'), 'building', 'aqua', 'superadmin/agencies',  $agency_salaries));
                $row->column(6, new InfoBox(__('online rooms Count'), 'users', 'aqua', 'superadmin/rooms',  $rooms));
                $row->column(6, new InfoBox(__('Bd Count'), 'briefcase', 'aqua', 'superadmin/usersBD', $bdCount));
                $row->column(6, new InfoBox(__('you Wallet'), 'money', 'green', '/', $diAuth . ' 💰'));
            });
    }
}
