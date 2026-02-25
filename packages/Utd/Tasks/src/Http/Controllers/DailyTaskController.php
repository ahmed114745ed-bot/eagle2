<?php

namespace Utd\Tasks\Http\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Utd\Tasks\Entities\DailyTask;
use Utd\Tasks\Entities\Day;

class DailyTaskController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DailyTask';

    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid());
    }

    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    public function update($id)
    {
        $id = request()->route('id');

        return $this->form()->update($id);
    }

    public function edit($id, Content $content)
    {
        $id = request()->route('id');
        $form = $this->form()->edit($id);

        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($form);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $dayId = request('day_id');
        $grid = new Grid(new DailyTask());
        $grid->column('created_at')->hide();
        $grid->model()->where('day_id', $dayId);

        $grid->column('id', __('Id'));
        $grid->column('day_id', __('Day id'));
        $grid->column('title', __('Title'));
        $grid->column('type', __('Type'));
        $grid->column('sub_type', __('Sub-Type'));
        $grid->column('count', __('Count'));
        $grid->column('total_points', __('Total Points'));
        // $grid->column('title_ar', __('Title ar'));
        // $grid->column('title_en', __('Title en'));
        $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        $grid->tools(function ($tools) use ($dayId) {
            $day = Day::find($dayId);
            $createUrl = url('admin/days');
            $tools->append('<a href="'.$createUrl.'" class="btn btn-success btn-sm">الذهاب الي قائمه الايام</a>');
            $tools->append('<div><h5 style="color:yellow">قائمه مهام '.$day?->title.' </h5></div>');
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
        $show = new Show(DailyTask::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('day_id', __('Day id'));
        $show->field('type', __('Type'));
        $show->field('sub_type', __('Sub-Type'));
        $show->field('count', __('Count'));
        $show->field('total_points', __('Total Points'));
        $show->field('title_ar', __('Title ar'));
        $show->field('title_en', __('Title en'));
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

        $dayId = request('day_id');
        if ($dayId) {
            $form->hidden('day_id')->default($dayId);
        } else {
            // $form->belongsTo('day_id', Days::class, 'Day');
            $form->select('day_id', 'Day')
                ->options(Day::pluck('title', 'id'))
                ->required();
        }

        $form->select('type', trans('type'))->options([
            'spend_coins' => __('Spend Coins'),
            'send_gift' => __('Send Gift'),
            'send_voice_room_gift' => __('Send Voice Room Gift'),
            'live_minutes' => __('Live Minutes'),
            'follow_anchors' => __('Follow Anchors'),
            'listen_live_minutes' => __('Listen Live Minutes'),
            'achievement' => __('achievement'),
            'vip' => __('Vip'),
        ])->when('send_gift', function (Form $form) {
            $form->text('sub_type', 'Sub Type');
        });

        $form->number('count', __('count'));
        $form->number('total_points', __('total_points'));
        $form->text('title_ar', __('Title ar'));
        $form->text('title_en', __('Title en'));

        // $form->datetime('created_at', __('Created at'))->default(date('Y-m-d H:i:s'));
        // $form->datetime('updated_at', __('Updated at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }

    protected function typeOptions()
    {
        return [
            'spend_coins' => 'Spend Coins',
            'send_gift' => 'Send Gift',
        ];
    }
}
