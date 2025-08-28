<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;
use App\Models\TimeEnterRoom;
use App\Models\RealtimeProject;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\InfoBox;
use App\Admin\Controllers\MainController;
use Encore\Admin\Widgets\Table;


class TimeEnterRoomController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'TimeEnterRoom';
    public $permission_name = 'time-room-report';

    public function index(Content $content)
    {

        $realTime = RealtimeProject::where('type', 'audio')->where('month', now()->month)->where('year', now()->year)->first();
        $remaining = $realTime->balance - $realTime->used;
        return parent::index($content
            ->title(trans("Reports"))
            ->row(function (Row $row) use ($remaining) {
                $row->column(6, new InfoBox(__('remaining minutes'), 'clock', 'primary', null, $remaining));
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            })->row(view('admin.same_device_users_modal')));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new TimeEnterRoom());
        $grid->disableRowSelector();


        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->expand();

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('room_id', __('room id'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $from = request('from_date');
                }, __('From Date'), 'from_date')->date();
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $to = request('to_date');
                }, __('To Date'), 'to_date')->date();
            });
        });
        $start = convertArabicToEnglishNumbers(request('from_date'));
        $end = convertArabicToEnglishNumbers(request('to_date'));
        $grid->model()->when(!empty($start) && !empty($end), function ($query) use ($start, $end) {

            $query->whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        })
            ->selectRaw('user_id, room_id, SUM(minutes) as total_minutes, COUNT(*) as total_records')
            ->groupBy('user_id', 'room_id');
        $grid->column('user_id', __('user id'));
        $grid->column('user.name', __('user'));
        $grid->column('room_id', __('room id'));

        $grid->column('custom_button2', __('times'))->display(function () {

            return "<button class='btn btn-sm btn-primary show-same-device-modal' 
            data-user-id='{$this->user_id}' 
            data-room-id='{$this->room_id}'>
            View Times
        </button>";
        });

        Admin::script("
            $(document).on('click', '.show-same-device-modal', function() {
                var userId   = $(this).data('user-id');
                var roomId   = $(this).data('room-id');
                var fromDate = '" . request('from_date') . "';
                var toDate   = '" . request('to_date') . "';

                $('#timeUsersModal .modal-body').html('Loading...');
                $('#timeUsersModal').modal('show');

                $.get('/admin/time-user-room', { 
                    user_id: userId, 
                    room_id: roomId, 
                    from_date: fromDate, 
                    to_date: toDate 
                }, function(html) {
                    $('#timeUsersModal .modal-body').html(html);
                });
            });
        ");



        $grid->disableActions();
        return $grid;
    }


    public function userTime(Request $request)
    {
        
        $start = convertArabicToEnglishNumbers($request->from_date);
        $end = convertArabicToEnglishNumbers($request->to_date);
        
        $timeRooms = TimeEnterRoom::where('room_id', $request->room_id)->where('user_id', $request->user_id)
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay()
                ]);
            })
            ->orderByDesc('id')->get();
        $rows = $timeRooms->map(function ($TimeRooms) {
            $start = Carbon::createFromTimestamp($TimeRooms->start_time)->format('m.d H:i:s');
            $end   = @$TimeRooms?->end_time ? Carbon::createFromTimestamp(@$TimeRooms?->end_time)->format('m.d H:i:s') : 'onGoing';

            return [
                'start' => $start,
                'end'   => $end ?? 'onGoing',
            ];
        });

        $table = new Table([__('start'), __('end')], $rows->toArray());
        // Return just table's HTML (your AJAX will inject this)
        return $table->render();
    }
}
