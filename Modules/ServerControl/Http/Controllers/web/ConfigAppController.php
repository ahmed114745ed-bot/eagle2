<?php

namespace Modules\ServerControl\Http\Controllers\web;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\Whatsapp\Entities\WhatsappApp;

class ConfigAppController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ConfigApp';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WhatsappApp());
        $grid->model()->where("type","config");
        $grid->column('id', __('Id'));
        $grid->column('uuid', __('uuid'));
        $grid->column('username', __('name'));
        $grid->column('password', __('Password'));
        $grid->column('webhook_url', __('Webhook url'));
        $grid->column('phone', __('Phone'));
        $grid->column('phone_id', __('Phone Number Id'));


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
        $show = new Show(WhatsappApp::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uuid', __('uuid'));
        $show->field('username', __('name'));
        $show->field('password', __('Password'));
        $show->field('webhook_url', __('Webhook url'));
        $show->field('phone', __('Phone'));
        $show->field('phone_id', __('Phone Number Id'));


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WhatsappApp());
        $form->hidden('type', __('name'))->value("config");
        $form->hidden('config', __('name'))->value(1);
        $form->text('username', __('name'));
        $form->password('password', __('Password'))->attribute('onfocus', "this.removeAttribute('readonly');")->attribute('readonly');
        $form->text('webhook_url', __('Webhook url'));
        $form->text('phone', __('Phone'));
        $form->text('phone_id', __('Phone Number Id'));

        return $form;
    }
}
