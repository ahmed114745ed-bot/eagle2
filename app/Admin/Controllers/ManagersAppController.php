<?php

namespace App\Admin\Controllers;

use App\Models\Manager;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ManagersAppController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Manager Zones App';
    public function index(Content $content)
    {
        return $content
            ->title(trans('manager_zones_app'))
            ->body($this->grid());
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Manager());

        $grid->column('id', __('Id'));
        // $grid->column('name', __('Name'));
        // $grid->column('email', __('Email'));
        $grid->column('user_info', __('user_info'))->display(function () {
            $defaultImage = asset("images/default-user.jpg"); // صورة افتراضية
            $avatarPath = $this->avatar;
            $avatar = getImagePath($avatarPath) ?? $defaultImage;
        
            if (!isImageExists($avatar)) {
                $avatar = $defaultImage;
            }
        
            $name = $this->name;
            $email = $this->email;
            $userId = $this->id;
        
            return "<div style='display: flex; align-items: center; gap: 10px; cursor: pointer;' onclick=\"window.location.href='/admin/managers/$userId'\">
                        <img src='$avatar' alt='User Avatar' style='width: 40px; height: 40px; border-radius: 50%; object-fit: cover;'>
                        <div>
                            <span style='color: #3498db; font-weight: bold;'>$name</span><br>
                            <span style='color: #aaa; font-size: smaller;'>📧 $email</span>
                        </div>
                    </div>";
        });
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
        
        // $grid->column('zone_id', __('zone'));
        // $grid->column('email_verified_at', __('Email verified at'));
        // $grid->column('password', __('Password'));
        // $grid->column('remember_token', __('Remember token'));
        $grid->column('created_at', __('created'));
        // $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Manager::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        // $show->field('zone_id', __('Zone id'));
        // $show->field('email_verified_at', __('Email verified at'));
        // $show->field('password', __('Password'));
        // $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        // $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Manager());

        $form->text('name', __('Name'));
        $form->email('email', __('Email'));
        // $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
        $form->password('password', __('Password'));
        // $form->text('remember_token', __('Remember token'));

        return $form;
    }
}
