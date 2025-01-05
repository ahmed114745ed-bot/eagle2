<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\RestoreUserAccount;
use App\Admin\Actions\SoftDeleteUserAccount;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;

class TrashedUserAccountController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    public $permission_name = 'trashed-account-user';
    use HasResourceActions;


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('User'))
            ->body($this->grid()));
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->onlyTrashed()->orderByDesc('deleted_at');

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('uuid', __('uuid'));
            });
        });
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('uuid', __('uuid'));
        $grid->column('phone', __('Phone'));
        $grid->column('deleted_at', __('Deleted at'))->diffForHumans();
        $grid->actions(function ($actions) {
            $model = $actions->row;
            $actions->add(new RestoreUserAccount($model->id));
            $actions->add(new SoftDeleteUserAccount($model->id)); 
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
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
        $show = new Show(User::findOrFail($id));

       
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        
        return $form;
    }
}
