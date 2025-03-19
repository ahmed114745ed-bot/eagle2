<?php

namespace App\Admin\Controllers;

use App\Models\Admin;
use App\Models\Manager;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class ManagersDashController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Manager Zones Dash';
    public function index(Content $content)
    {
        return $content
            ->title(trans('manager_zones_dash'))
            ->body($this->grid());
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Admin());
        $grid->model()->where('app_manager_id', '!=', 0);
        $grid->column('id', __('Id'));
        // $grid->column('username', __('Username'));
        // $grid->column('name', __('Name'));
        // $grid->column('avatar', __('Avatar'));
        // $grid->column('remember_token', __('Remember token'));
        $grid->column('user_info', __('user_info'))->display(function () {
            $defaultImage = asset("images/default-user.jpg"); // صورة افتراضية
            $avatarPath = $this->avatar;
            $avatar = getImagePath($avatarPath) ?? $defaultImage;
        
            if (!isImageExists($avatar)) {
                $avatar = $defaultImage;
            }
        
            $name = $this->name;
            $username = $this->username;
            $userId = $this->id;
        
            return "<div style='display: flex; align-items: center; gap: 10px; cursor: pointer;' onclick=\"window.location.href='/admin/admins-managers/$userId'\">
                        <img src='$avatar' alt='User Avatar' style='width: 40px; height: 40px; border-radius: 50%; object-fit: cover;'>
                        <div>
                            <span style='color: #3498db; font-weight: bold;'>$name</span><br>
                            <span style='color: #aaa; font-size: smaller;'>@$username</span>
                        </div>
                    </div>";
        });
        

        $grid->column('appManager.name', __('app_manager'))->display(function ($name) {
            $defaultImage = asset("images/manager-icon.jpg"); // صورة افتراضية
            $avatarPath = @$this->appManager->avatar;
            $managerId = @$this->appManager->id;
        
            $avatar = getImagePath($avatarPath) ?? $defaultImage;
        
            if (!isImageExists($avatar)) {
                $avatar = $defaultImage;
            }
        
            return "<div style='display: flex; align-items: center; gap: 10px; cursor: pointer;' onclick=\"window.location.href='/admin/managers/$managerId'\">
                        <img src='$avatar' alt='Manager Avatar' style='width: 40px; height: 40px; border-radius: 50%; object-fit: cover;'>
                        <div>
                            <span style='color: #3498db; font-weight: bold;'>$name</span><br>
                        </div>
                    </div>";
        });
        $grid->column('created_at', __('created'));

        $grid->column('zone_id', __('zone'))->display(function () {
            $defaultImage = asset("images/map-icon.png"); // صورة افتراضية للخريطة
            $zoneName = @$this->zone->name; // الحصول على اسم المنطقة
            $zoneId = $this->zone_id;
        
            return "<div style='display: flex; align-items: center; gap: 10px; cursor: pointer;' onclick=\"window.location.href='/admin/zones/$zoneId'\">
                        <div>
                            <span style='color: #3498db; font-weight: bold;'>$zoneName</span><br>
                            <span style='color: #aaa; font-size: smaller;'>#ID: $zoneId</span>
                        </div>
                    </div>";
        });
        

        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('Agency_manger', __('Agency manger'));
        // $grid->column('app_id', __('App id'));
        // $grid->column('is_preview', __('Is preview'));
        // $grid->column('app_manager_id', __('App manager id'));

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
        $show = new Show(Admin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('username'));
        // $show->field('password', __('Password'));
        $show->field('name', __('name'));
        $show->field('avatar', __('avatar'));
        // $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('created'));
        // $show->field('updated_at', __('Updated at'));
        // $show->field('Agency_manger', __('Agency manger'));
        // $show->field('app_id', __('App id'));
        // $show->field('is_preview', __('Is preview'));
        // $show->field('app_manager_id', __('App manager id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Admin());
    
        $form->text('username', __('username'))->required();
        $form->password('password', __('password'))->required();
        $form->text('name', __('name'))->required();
        $form->image('avatar', __('avatar'));
    
        $form->text('app_name', __('app_name'))->required();
        $form->email('email', __('Email'))->required();
        $form->password('app_password', __('app_password'))->required();
    
        $form->saving(function (Form $form) {
            try {

                $exists = Admin::where('username', $form->username)
                    ->when($form->model()->id, function ($query) use ($form) {
                        return $query->where('id', '!=', $form->model()->id);
                    })
                    ->exists();
    
                if ($exists) {
                    admin_error('The username is already taken. Please choose another.');
                    return false; // **إيقاف عملية الحفظ**
                }
    
                $appAdd = Manager::create([
                    'name' => $form->app_name,
                    'email' => $form->email,
                    'password' => Hash::make($form->app_password),
                ]);
                $form->model()->app_manager_id = $appAdd->id;
                if ($form->password) {
                    $form->password = Hash::make($form->password);
                }
                
              
            } catch (\Throwable $th) {
                admin_error($th->getMessage());
                return false;
            }
        });
    
        return $form;
    }
    
}
