<?php

namespace App\Admin\Controllers;

use App\Models\Color;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;

class ColorController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Color';
    public $permission_name = 'color';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Color());

        $grid->column('id', __('Id'));
        $grid->column('color', __('Color'));
        $grid->column('status', __('status'))->display(function ($status){
            return $status== 0?__('main colors'):__('button colors');
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
        $show = new Show(Color::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('color', __('Color'));
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
        $form = new Form(new Color());

        $form->color('color', __('Color'));
        $form->select('status', __('Status'))->options (
            [
                0=>__('main colors'),
                1=>__('button colors')
            ]
        );

        return $form;
    }
}
