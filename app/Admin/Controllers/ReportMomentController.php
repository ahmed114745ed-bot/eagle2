<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Extensions\CheckRow;
use Modules\Moment\Entities\Moment;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Modules\Moment\Entities\ReportMoment;
use Encore\Admin\Controllers\AdminController;

class ReportMomentController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
   
    public $permission_name = 'report-moment';

    public function index(Content $content)
    {
        return $content
            ->title(trans('report-moments'))
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->title(trans('report-moments'))
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('report-moments'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('report-moments'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportMoment());
        $grid->model()->whereHas('moment')->orderByDesc('id');
        $grid->model()->with(['moment' => fn($query) => $query->withExists(['likes','comments'])]);
        $grid->column('id', __('Id'));
        // $grid->column('moment_id', __('Moment id'));
        $grid->column('Reporter_id', __('Reporter id'));
        $grid->column('Reported_id', __('Reported id'));
        $grid->column('description', __('Description'));
        $grid->column('type', __('Type'));
        $grid->column(__('redirect_button'))->display(function ($_) {
            $redirectRoute = 'delete-moment';
            $button = '<a href="'.route($redirectRoute, ['moment_id' => $this->moment_id,'id' => $this->id]).'" class="btn btn-xs btn-primary">حذف اللحظة</a>';
            return $button;
        });

        $grid->column('moment_id', __('View Moment'))->modal('test', function ($model){
            return self::getRoomsShow($model->moment);
        });
        return $grid;
    }

    public static function getRoomsShow(Moment $moment){

        $show = new Show($moment);
        $show->field('id', 'ID');
        $show->field('img', __('Image'))->image(getDriverUrl() . DIRECTORY_SEPARATOR, 500, 500);
        $show->field('description', __('description'));
        $show->field('likes_exists', __('Likes count'))->number();
        $show->field('comments_exists', __('Comments count'))->number();

        $show->panel()
             ->tools(function ($tools) {
                 $tools->disableEdit();
                 $tools->disableList();
                 $tools->disableDelete();
             });

        return $show;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ReportMoment::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('moment_id', __('Moment id'));
        $show->field('Reporter_id', __('Reporter id'));
        $show->field('Reported_id', __('Reported id'));
        $show->field('description', __('Description'));
        $show->field('type', __('Type'));
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
        $form = new Form(new ReportMoment());

        $form->number('moment_id', __('Moment id'));
        $form->number('Reporter_id', __('Reporter id'));
        $form->number('Reported_id', __('Reported id'));
        $form->text('description', __('Description'));
        $form->text('type', __('Type'));

        return $form;
    }
}
