<?php

namespace App\Admin\Controllers;

use App\Models\ChangeLevelHistory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ChangeLevelHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ChangeLevelHistory';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ChangeLevelHistory());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('user.uuid', __('uuid'));
            });
        });

        $grid->column('id', __('Id'));
        $grid->column('user.name', __('User'))
            ->display(function ($name) {
                $uid = @$this->user->uuid;
                $path = @$this->user?->profile?->avatar;
                $url = getImagePath($path) ?? asset("images/businessman-icon.jpg");
                $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <div>
                    <strong>$name</strong><br>
                    <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                </div>
            </div>
        ";
            });
        $grid->column('admin.name', __('admin'))
            ->display(function ($name) {
                $path = @$this->admin->avatar;
                $url = getImagePath($path) ?? asset("images/businessman-icon.jpg");
                $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                return "
            <div style='display: flex; align-items: center; gap: 10px;'>
                $image
                <span>$name</span>
            </div>
        ";
            });
        $grid->column('old_total_sender_level', __('Old total sender level'));
        $grid->column('new_total_sender_level', __('New total sender level'));
        $grid->column('old_total_received_level', __('Old total received level'));
        $grid->column('new_total_received_level', __('New total received level'));
        $grid->column('created_at', __('Created at'))->diffForHumans();



        $grid->disableActions();
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
        $show = new Show(ChangeLevelHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('admin_id', __('Admin id'));
        $show->field('old_total_sender_level', __('Old total sender level'));
        $show->field('new_total_sender_level', __('New total sender level'));
        $show->field('old_total_received_level', __('Old total received level'));
        $show->field('new_total_received_level', __('New total received level'));
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
        $form = new Form(new ChangeLevelHistory());

        $form->number('user_id', __('User id'));
        $form->number('admin_id', __('Admin id'));
        $form->number('old_total_sender_level', __('Old total sender level'));
        $form->number('new_total_sender_level', __('New total sender level'));
        $form->number('old_total_received_level', __('Old total received level'));
        $form->number('new_total_received_level', __('New total received level'));

        return $form;
    }
}
