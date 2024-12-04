<?php

namespace App\Admin\Controllers;

use App\Models\DeleteAccount;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DeleteAccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DeleteAccount';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeleteAccount());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('image', __('Image'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(DeleteAccount::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('image', __('Image'));
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
        $form = new Form(new DeleteAccount());

        $form->table('entries', __('Entries'), function ($table) {
            $table->textarea('title', __('Title'));
            $table->image('image', __('Image'));
        });
    
        return $form;
    }
}
