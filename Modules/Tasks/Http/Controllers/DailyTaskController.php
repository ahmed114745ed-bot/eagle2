<?php

//namespace App\Admin\Controllers;

//use App\Models\DailyTask;

namespace Modules\Tasks\Http\Controllers;//App\Admin\Controllers;

use Modules\Tasks\Entities\DailyTask;
use Modules\Tasks\Entities\Day;
use Modules\Tasks\Selectable\Days; 
use Illuminate\Support\Facades\Request;
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
        $dayId = Request::get('day_id');
//disable create button
        if ($dayId) {
            $grid->model()->where('day_id', $dayId);
        }

        $grid->column('id', __('Id'));
        $grid->column('day_id', __('Day id'));
        $grid->column('title', __('Title'));
        $grid->column('type', __('Type'));
        $grid->column('sub_type', __('Sub type'));
        $grid->column('count', __('Count'));
        $grid->column('total_points', __('Total points'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        $grid->tools(function ($tools) {
            $dayId = request()->get('day_id');
            $createUrl = route('daily-tasks.create', ['day_id' => $dayId]);
            $tools->append('<a href="' . $createUrl . '" class="btn btn-success">Create</a>');
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

    $dayId = request()->get('day_id');

    if ($dayId) {
        $form->hidden('day_id')->default($dayId);
    } else {
        $form->select('day_id', 'Day')
            ->options(Day::pluck('title', 'id'))
            ->required();
    }

    $form->select('type', __('Type'))
        ->options([
            'images' => 'Images',
            'video' => 'Video',
            'enter_room' => 'Enter Room',
            'background_room' => 'Background Room'
        ])
        ->required()
        ->when('images', function (Form $form) {

            $form->select('sub_type', __('Sub Type'))
                ->options([
                    'profile' => 'Profile',
                    'row' => 'Row'
                ])
                ->placeholder('Select Sub Type')
                ->default(null);

        });




    $form->text('title', __('Title'))->required();

    $form->number('count', __('Count'))
        ->attribute(['step' => 1])  
        ->min(0)
        ->default(0)
        ->required();

    $form->number('total_points', __('Total Points'))
        ->attribute(['step' => 1])
        ->min(0)
        ->default(0)
        ->required();


        $form->saving(function (Form $form) use ($dayId) {
            if ($dayId) {
                $form->day_id = $dayId;
            }
        });
    
    return $form;
}


}
