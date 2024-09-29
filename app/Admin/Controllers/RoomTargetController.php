<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\User;
use App\Models\RoomTarget;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class RoomTargetController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'room-target';

    protected function grid()
    {

        $grid = new Grid(new RoomTarget);
        $grid->column('coins',__("coins"));
        $grid->column('usd',__("usd"));
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RoomTarget::findOrFail($id));
        $this->extendShow ($show);
        return $show;
    }

    protected function form()
    {
        $form = new Form(new RoomTarget);
        $form->number('coins', 'coins');
        $form->number('usd', 'usd');
        return $form;
    }
}
