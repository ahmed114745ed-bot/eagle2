<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ResendZegoMessageAction;
use App\Models\User;
use App\Traits\Gifts\WinLuckyGift;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\CustomZegoMessage;
use App\Models\Gift;
use Encore\Admin\Controllers\AdminController;

class CustomZegoMessageController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'CustomZegoMessage';
    use WinLuckyGift;
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomZegoMessage());
        $grid->model()->with(['user', 'gift']);

        $grid->column('id', __('Id'));
        $grid->column('user.name', __('name'));
        $grid->column('user.uuid', __('uuid'));
        $grid->column('gift.img',trans ('image'))->image ('','30');
        $grid->actions (function ($actions){
            $actions->add(new ResendZegoMessageAction());
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CustomZegoMessage::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('room_id', __('Room id'));
        $show->field('percentage', __('Percentage'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CustomZegoMessage());

        $form->select('user_id', __('user'))->options('/api/search/users2')->ajax('/api/search/users2', 'id', 'name');
        $form->select('gift_id', __('gifts'))->options(
            Gift::where('type', 6)->get()->pluck(null, 'id')->map(fn($gift) => $gift->name . ' - ' . $gift->id . ' - ' . $gift->price)->toArray()
        )->required();
        $form->number('percentage', __('Percentage'));
        $form->saving(function (Form $form) {
            $user=User::query()->find($form->user_id);
            $gift=Gift::query()->find($form->gift_id);
            $room=$user->ownerRoom;

                $zigoData = [
                    'user_id'      => $user->id,
                    'user_image'   => @$user->profile->avatar ?? '',
                    'gift_image'   => @$gift->img ?? '',
                    'owner_id'     => $user->id,
                    'user_name'    => $user->name ?? '',
                    'room_id'      => $room->id,
                    'percentage'   => $form->percentage,
                    'is_room_pass' => ($room->room_pass != null && $room->room_pass != ''),
                    'gift_price'   => @$gift->price,
                    'room_name'   => $room->room_name ?: '',
                    'room_cover'   => $room->room_cover ?? '',
                    'room_background'   =>$room->final_room_image ?? '', 
                    'room_mode'   =>  $room->mode,
                    'room_uuid'   => $room->owner?->uuid ?: 0,
                    'room_owner_id'   => $room->uid ?: 0,
                    'is_password'   =>  (bool)(@$room->room_pass),

                ];
                $this->sendToZegoLuckyGift($zigoData);
        });
        return $form;
    }
}
