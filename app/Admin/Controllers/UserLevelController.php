<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserLevelController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User Levels';

    public $permission_name = 'user-levels';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->quickSearch ();
        $grid->filter (function (Grid\Filter $filter){
            $filter->expand ();
            $filter->column(1/2, function ($filter) {
                $filter->equal('uuid',__ ('uuid'));
            });
        });

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));

        $grid->column('uuid', __('uuid'));
        $grid->column('total_sender_level', __('Sender Level'));
        $grid->column('total_received_level', __('Received Level'));
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->disableCreateButton();
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
//        $show = new Show(User::findOrFail($id));

        return null;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text ('uuid', __('uuid'))->updateRules('unique:users,uuid,{{id}}')->creationRules('unique:users,uuid')->required();
        $form->number ('total_sender_level', __('Sender Level'))->default(0);
        $form->number ('total_received_level', __('Received Level'))->default(0);

        return $form;
    }
}
