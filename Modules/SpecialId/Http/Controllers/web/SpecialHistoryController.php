<?php

namespace Modules\SpecialId\Http\Controllers\web;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\SpecialId\Entities\SpecialHistory;

class SpecialHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SpecialHistory';
    public $permission_name = 'special-history';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SpecialHistory());

        $grid->column('id', __('Id'));
        $grid->column('user.name', __('name'));
        $grid->column('user.uuid', __('uuid'));
        $grid->column('ware.show_img', __('image'))->image('', 50);
        $grid->column('status', __('status'))->display(function ($status) {
         // استخدم الشهر والسنة كمعاملات إذا لزم الأمر
            return $status == 1 ? "<span class='label-success' " .'style="width: 8px;height: 8px;padding: 0;border-radius: 50%;display: inline-block;"'.
                "></span>" : "<span class='label-warning' " .'style="width: 8px;height: 8px;padding: 0;border-radius: 50%;display: inline-block;"'.
                "></span>";
        });
        
        $grid->column('created_at', trans('admin.created_at'))->diffForHumans ();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableView();
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
        $show = new Show(SpecialHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('status', __('Status'));
        $show->field('user_id', __('User id'));
        $show->field('ware_id', __('Ware id'));
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
        $form = new Form(new SpecialHistory());

        $form->switch('status', __('Status'));
        $form->number('user_id', __('User id'));
        $form->number('ware_id', __('Ware id'));

        return $form;
    }
}
