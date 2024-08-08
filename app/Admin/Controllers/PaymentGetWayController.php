<?php

namespace App\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\PaymentGateway;
use Encore\Admin\Controllers\AdminController;

class PaymentGetWayController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'paymentGateway';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PaymentGateway());

        $grid->column('id', __('Id'));
        $grid->column('title', __('title'));
        $grid->column('photo', __('Photo'))->image('', 50);

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
        $show = new Show(PaymentGateway::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('photo', __('Photo'));
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
        $form = new Form(new PaymentGateway());

        $form->text('title', __('Title'));
        $form->image('photo', __('Photo'));

        return $form;
    }
}
