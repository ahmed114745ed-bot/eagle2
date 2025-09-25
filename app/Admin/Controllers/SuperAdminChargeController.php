<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\SuperAdminChargeAction;
use App\Models\SuperAdmin;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\HasResourceActions;

class SuperAdminChargeController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'charge-to-superadmin';
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        if (! Admin::user()->can('*')) {
            Permission::check('browse-' . $this->permission_name);
        }
        return $content
            ->title(trans('charges'))
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SuperAdmin());
        $grid->disableRowSelector();

        $grid->filter(function (Grid\Filter $filter) {

            $filter->expand();

            $filter->disableIdFilter();
            $filter->equal('ID', __('ID'));

            $filter->where(function ($query) {
                $query->where('name', 'like', "%{$this->input}%");
            }, __('name'));
        });

        $grid->model()
            ->select('id', 'name', 'di')
//            ->with('profile')
            ->orderByDesc('id');

        $grid->id(__('ID'));



        $grid->column('name', trans('owner'))
            ->display(function ($name) {
                $id = @$this->id;
                $path = @$this?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                $image = handleShowImageWithTypes($this->id, $url, 40, 40);
                $showUrl = $this ? url("admin/users/{$this->id}") : 0;
                return "
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        $image
                        <div>
                           <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                             <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                            </a>
                            <span style='color: #aaa; font-size: smaller;'>Id: $id</span>
                        </div>
                    </div>";
            });

        $grid->column('di', __('coins'))->display(function ($coin) {
            $icon = asset('images/coin.jpg');
            $coin = (float) $coin;
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });

        if (Admin::user()->can('add-switch-' . $this->permission_name) || Admin::user()->can('*') || Admin::user()->can('history-switch-' . $this->permission_name)) {
            $grid->column('actions', __('Actions'))
                ->display(function () {

                    return (new SuperAdminChargeAction())->setUserId($this->id)->render();
                })
                ->style('white-space: nowrap; width: 100px;');
        }

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableActions();

        return $grid;
    }
}
