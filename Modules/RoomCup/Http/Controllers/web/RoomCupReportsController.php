<?php

namespace Modules\RoomCup\Http\Controllers\web;

use App\Models\User;
use App\Models\Room;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\RoomCup\Entities\RoomCupReward;
use Modules\RoomCup\Entities\TotalRoomGift;
use Encore\Admin\Layout\Content;
use App\Admin\Services\UserService;

class RoomCupReportsController extends AdminController
{
    protected $title = '';

    public function index(Content $content)
    {
        return $content
        ->header(__('Room Cup Daily Rewards'))
        ->description(__('Room Cup Daily Rewards'))
        ->body($this->grid());
    }
    protected function grid()
    {
        $grid = new Grid(new RoomCupReward());
        $grid->model()
        ->with(['gift:id,current_total', 'gift.room','user.packs', 'user.profile',
        
        'gift.room.owner:id,id,name,uuid',
        'gift.room.owner.packs',
        'gift.room.owner.profile:id,user_id,avatar',
        
        ])
        ->orderBy('created_at', 'desc');

   
        $grid->column('user_id', __('user'))->display(function ($name) {
            $user = $this->user;
            if (! $user) {
                return __('No User');
            }

            return app(UserService::class)->adminUserAvatar($user,withoutLevels: true);
        });
           
        $grid->column('owner', __('room owner'))->display(function ($name) {
            $user = $this->room->owner;
            if (! $user) {
                return __('No User');
            }

            return app(UserService::class)->adminUserAvatar($user,withoutLevels: true);
        });

        $grid->column('room_id', __('room'))->display(function ($name) {
            $path = @$this->room->room_cover;
            $id = @$this->room->id;
            $defaultImage = asset("images/room.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            if (strlen($name) > 50){
                $name = substr($name,0,50) . ' ...';
            }
            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    <img src='$url' alt='Room Image' style='width: 50px; height: 50px; object-fit: cover; border-radius: 6px;'>
                    <div>
                        <span style='cursor: pointer;'>$name</span><br>
                        <span style='cursor: pointer;'>ID: $id</span>
                    </div>
                </div>
            ";
        });

    

        $grid->column('gift.current_total', __('Daily Gain'));

        $grid->column('amount', __('Profit Coins'));
        $grid->column('type', __('type'));

        // 🔹 Date of gain
        $grid->column('created_at', __('Date'))->display(function ($date) {
            return \Carbon\Carbon::parse($date)->toDateString();
        });

        // 🔹 Filters
        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->like('gift.room.owner.name',  __('Owner Name'));
            $filter->equal('gift.room.id',  __('Room ID'));
            $filter->between('created_at', __('Date'))->date();
            $filter->where(function ($query) {
                $query->where('gain_value', '>', $this->input);
            }, __('Gain >'));
        
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
