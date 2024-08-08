<?php

namespace App\Admin\Controllers;

use App\Models\AppFeature;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AppFeatureController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'AppFeature';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AppFeature());

        $grid->column('id', __('Id'));
        $grid->column('name_ar', __('name'));
        $grid->column('name', __('name_en'));
        $grid->column('slug', __('slug'));
        $grid->column('status', __('status'));
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
        $show = new Show(AppFeature::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('name_ar', __('Name ar'));
        $show->field('slug', __('Slug'));
        $show->field('status', __('Status'));
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
        $form = new Form(new AppFeature());

        
        $form->text('name_ar', __('name '));
        $form->text('name', __('name_en'));
        $form->text('slug', __('Slug'));
        $state = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];

        $form->switch('status', __("status"))->states($state);

        return $form;
    }
}
