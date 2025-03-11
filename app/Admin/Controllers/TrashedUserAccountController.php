<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use App\Admin\Actions\RestoreUserAccount;
use App\Admin\Controllers\MainController;
use App\Admin\Actions\SoftDeleteUserAccount;
use Encore\Admin\Controllers\HasResourceActions;

class TrashedUserAccountController extends  MainController
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
            ->title(trans('Deleted Accounts'))
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
        // $grid->column('name', __('Name'));
        // $grid->column('uuid', __('uuid'));
        $grid->column('name', __('User Info'))->display(function () {
            $name = $this->name;
            $uuid = $this->uuid;
            $phone = $this->phone ?: '-'; // إذا لم يكن هناك رقم هاتف، عرض "-"
            $defaultImage = asset("images/businessman-icon.jpg");
            $avatarPath = @$this->avatar;
            $avatar = getImagePath($avatarPath) ?? $defaultImage;
    
            if (!isImageExists($avatar)) {
                $avatar = $defaultImage;
            }
    
            $userUrl = admin_url('users/' . $this->id);
    
            return "<div style='display: flex; align-items: center; gap: 10px; background: var(--bg-color); padding: 10px; border-radius: 8px;'>
                        <img src='$avatar' alt='User Avatar' style='width: 40px; height: 40px; border-radius: 50%;'>
                        <div>
                            <a href='$userUrl' style='color: var(--primary-color); font-weight: bold; text-decoration: none;'>$name</a><br>
                            <span style='color: var(--text-primary-color); font-size: smaller;'>UUID: $uuid</span><br>
                            <span style='color: var(--text-primary-color); font-size: smaller;'>📞 $phone</span>
                        </div>
                    </div>";
        });
    
        // $grid->column('phone', __('Phone'));
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
