<?php

namespace Modules\RoomCup\Http\Controllers\web;

use App\Models\User;
use App\Models\Room;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\RoomCup\Entities\RoomCupReward;
use Modules\RoomCup\Entities\TotalRoomGift;

class RoomCupReportsController extends AdminController
{
    protected $title = 'Room Cup Daily Rewards';

    protected function grid()
    {
        $grid = new Grid(new RoomCupReward());

        $grid->model()
            ->with(['gift', 'gift.room', 'gift.room.owner'])
            ->orderBy('created_at', 'desc');

        // 🔹 Owner Data
        $grid->column('gift.room.owner.name', 'Owner Name');
        $grid->column('gift.room.owner.id', 'Owner ID');

        // 🔹 Room Data
        $grid->column('gift.room.id', 'Room ID');
        $grid->column('gift.room.name', 'Room Name');

        // 🔹 Daily Gain (from TotalRoomGift)
        $grid->column('gift.current_total', 'Daily Gain');

        // 🔹 Profit in Coins (from reward amount)
        $grid->column('amount', 'Profit Coins')->totalRow();

        // 🔹 Date of gain
        $grid->column('created_at', 'Date')->display(function ($date) {
            return \Carbon\Carbon::parse($date)->toDateString();
        });

        // 🔹 Filters
        $grid->filter(function ($filter) {
            $filter->like('gift.room.owner.name', 'Owner Name');
            $filter->equal('gift.room.id', 'Room ID');
            $filter->between('created_at', 'Date')->date();
            $filter->where(function ($query) {
                $query->whereHas('gift', function ($q) {
                    $q->where('current_total', '>=', $this->input);
                });
            }, 'Gain >= X');
        });

        $grid->disableCreateButton();
        $grid->disableActions();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RoomCupReward::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('room_id', 'Room ID');
        $show->field('user_id', 'User ID');
        $show->field('amount', 'Amount');
        $show->field('type', 'Type');
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }
}
