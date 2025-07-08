<?php

namespace App\Admin\Controllers;

use App\Models\AdminUser;
use App\Models\CoreWallets;
use App\Models\CoreWalletTransaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CoreWalletTransactionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '';


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CoreWalletTransaction());
    
        $grid->model()->latest();
        $grid->column('id', __('#'));
        $grid->column('from_wallet', __('From Wallet'))->display(function ($val) {
            return    ucfirst(str_replace('_', ' ', optional(CoreWallets::find($val))->name)) ;

            
        });
        $grid->column('to_wallet', __('To Wallet'))->display(function ($val) {
            return    ucfirst(str_replace('_', ' ', optional(CoreWallets::find($val))->name)) ;
        });
        $grid->column('amount', __('Amount'))->display(function ($val) {
            return $val;
        });
        $grid->column('admin_id', __('By'))->display(function ($val) {
            return optional(AdminUser::find($val))->name ?? __('Unknown');
        });
        $grid->column('created_at', __('Transfer Time'))->display(function ($val) {
            return \Carbon\Carbon::parse($val)->format('Y-m-d H:i');
        })->sortable(); 
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
        $show = new Show(CoreWalletTransaction::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('from_wallet', __('From wallet'));
        $show->field('to_wallet', __('To wallet'));
        $show->field('admin_id', __('Admin id'));
        $show->field('amount', __('Amount'));
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
        $form = new Form(new CoreWalletTransaction());

        $form->text('from_wallet', __('From wallet'));
        $form->text('to_wallet', __('To wallet'));
        $form->text('admin_id', __('Admin id'));
        $form->decimal('amount', __('Amount'));

        return $form;
    }
}
