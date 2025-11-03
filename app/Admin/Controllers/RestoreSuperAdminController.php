<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\SuperAdmin\Entities\SuperAdmin;
use Encore\Admin\Facades\Admin;
use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;

class RestoreSuperAdminController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SuperAdmin';
    public $permission_name = 'restore-super-admin';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('restore super admin'))
            ->body($this->grid()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SuperAdmin());
        $grid->model()->onlyTrashed()->orderBy('deleted_at', 'desc');

        $grid->filter(function ($filter) {
            $filter->like('appUser.uuid', __('App User UUID'));
            $filter->like('appUser.name', __('User Name'));
        });
        $grid->column('id', __('Id'));
        $grid->column('username', __('Super Admin'))->display(function ($name) {
            if (request()->filled('_export_')) {
                return $name;
            }

            $id = $this->id ?? '-';
            $name = $this->username ?? 'غير معروف';
            $path = $this->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/superadmin-users/{$this->id}");

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='font-size: smaller;'>ID: $id</span>
                    </div>
                </div>
            ";
        });
        $grid->column('appUser.name', __('user'))->display(function ($name) {
            $user = $this->appUser;
            if (request()->filled('_export_')) {
                return $name;
            }
            if (!$user) return "<span style='color: red;'>غير مرتبط</span>";

            $uid = $user->uuid ?? 'غير معروف';
            $path = $user->profile?->avatar;
            $defaultImage = asset("images/businessman-icon.jpg");
            $url = getImagePath($path) ?? $defaultImage;

            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $image = handleShowImageWithTypes($this->id, $url, 40, 40);
            $showUrl = url("admin/users/{$user->id}");

            return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                       <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                         <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='font-size: smaller;'>UUID: $uid</span>
                    </div>
                </div>
            ";
        });

        $grid->column('country.name', __('country'));
        if (Admin::user()->can('restore-switch-' . $this->permission_name) || Admin::user()->can('*')) {
            $grid->column('return', __('restore'))->display(function () {
                $superAdmin = SuperAdmin::where('country_id', $this->country_id)->first();
                return  $superAdmin ? __('can not restore this super admin') : (new \App\Admin\Actions\RestoreSuperAdminAction($this->id))->render();
            });
        }

        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        return $grid;
    }
}
