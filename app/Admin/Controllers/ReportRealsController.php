<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\Reals\Entities\ReportReals;
use App\Models\Ban;
use App\Http\Controllers\Controller;
use App\Models\User;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;


class ReportRealsController extends AdminController
{
    use HasResourceActions;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ReportReals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

     public function index(Content $content)
     {
        return $content
        ->header(trans('admin.index'))
        ->description(trans('admin.description'))
        ->body($this->grid());
     }
    protected function grid()
    {
        $grid = new Grid(new ReportReals());

        $grid->column('id', __('Id'));
        $grid->column('real_id', __('Real id'));
        $grid->column('Reporter_id', __('Reporter id'));
        $grid->column('Reported_id', __('Reported id'));
        $grid->column('description', __('Description'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
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
        $show = new Show(ReportReals::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('real_id', __('Real id'));
        $show->field('Reporter_id', __('Reporter id'));
        $show->field('Reported_id', __('Reported id'));
        $show->field('description', __('Description'));
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
        $form = new Form(new ReportReals());

        $form->number('real_id', __('Real id'));
        $form->number('Reporter_id', __('Reporter id'));
        $form->number('Reported_id', __('Reported id'));
        $form->text('description', __('Description'));

        return $form;
    }
}
