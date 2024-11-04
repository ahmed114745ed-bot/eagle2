<?php

namespace App\Admin\Controllers;

use App\Models\DailyTask;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DailyTaskController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DailyTask';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DailyTask());

        $grid->column('id', __('Id'));
        $grid->column('day_id', __('Day id'));
        $grid->column('title', __('Title'));
        $grid->column('type', __('Type'));
        $grid->column('sub_type', __('Sub type'));
        $grid->column('count', __('Count'));
        $grid->column('total_points', __('Total points'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(DailyTask::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('day_id', __('Day id'));
        $show->field('title', __('Title'));
        $show->field('type', __('Type'));
        $show->field('sub_type', __('Sub type'));
        $show->field('count', __('Count'));
        $show->field('total_points', __('Total points'));
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
        $form = new Form(new DailyTask());

        $form->number('day_id', __('Day id'));
        $form->text('title', __('Title'));
        $form->text('type', __('Type'));
        $form->text('sub_type', __('Sub type'));
        $form->number('count', __('Count'));
        $form->number('total_points', __('Total points'));

        return $form;
    }
}
