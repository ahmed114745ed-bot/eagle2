<?php

namespace App\Admin\Controllers;

use App\Models\CoreWallets;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CoreWalletsController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'CoreWallets';

    public $permission_name = 'core-wallets';
    

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CoreWallets());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('coins', __('coins'));
        $grid->column('update_for_human', __('Updated at'));

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
        $show = new Show(CoreWallets::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('coins', __('Coins'));
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
        $form = new Form(new CoreWallets());

        $form->text('name', __('Name'));
        $form->number('coins', __('Coins'));

        return $form;
    }
}
