<?php

namespace Utd\Tasks\Http\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Utd\Tasks\Entities\Day;

class DayController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Day';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Day());

        $grid->column('id', __('Id'));
        $grid->column('day_number', __('Day Number'));
        $grid->column('title', __('Title'));
        $grid->column('is_unlocked', __('Is Unlocked'))->display(function ($isUnlocked) {
            return $isUnlocked ? '<span style="color: green;">Unlocked</span>' : '<span style="color: red;">Locked</span>';
        });
        $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        $grid->actions(function ($actions) {
            $day = $actions->row;

            $tasksUrl = url("admin/{$day->id}/day-tasks");
            $rewardsUrl = url("admin/{$day->id}/day-rewards");

            $actions->append('<a href="'.$tasksUrl.'" class="btn btn-sm btn-success" title="Tasks"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg></a>');
            $actions->append('<a href="'.$rewardsUrl.'" class="btn btn-sm btn-warning" title="Rewards"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-gift"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg></a>');
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Day::findOrFail($id));

        $show->field('id', __('Id'));
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
        $form = new Form(new Day());
        $form->number('day_number', 'Day Number');
        $form->text('title', 'title');
        $form->switch('is_unlocked', __('Is Unlocked'))->states([
            'on' => ['value' => 1, 'text' => 'Unlocked', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'Locked', 'color' => 'danger'],
        ])->default(0);

        return $form;
    }
}
