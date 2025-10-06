<?php

namespace App\Admin\Controllers;

use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Models\SuperAdmin;
use App\Selectables\Badges;
use Modules\Vip\Entities\OVip;
use App\Models\SuperAdminReward;
use App\Selectables\SuperAdmins;
use App\Selectables\WaresByType;
use Encore\Admin\Layout\Content;
use Modules\Badge\Entities\Badge;
use App\Admin\Controllers\MainController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;

class SuperAdminRewardController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SuperAdminReward';

    public $permission_name = 'super-admin-reward';
    public function index(Content $content)
    {

        if (!request()->has('type')) {
            return redirect()->to(url()->current() . '?type=vip');
        }

        session(['last_ware_type' => request()->get('type', 'vip')]);
        return parent::index($content
            ->title(trans('Super Admin Reward'))
            ->row(function (Row $row) {
                $row->column(12, $this->grid2());
            })
            ->row(function (Row $row) {
                $row->column(12, $this->tabsComponent());
            })
            ->row(function ($row) {
                $row->column(12, $this->grid());
            }));
    }

    private function tabsComponent()
    {
        $content = new Row();

        $types =  ['vip', 'ware', 'badge'];
        $currentType = request()->get('type', 'vip');

        $box = new Box(content: view('admin.grid.Form.rewardTabs', [
            'types' => $types,
            'currentType' => $currentType
        ]));

        $content->column(12, $box);

        return $content;
    }

    protected function grid2()
    {
        return (new Box(
            title: __('admin.description'),
            content: view('admin.grid.superadmin.description'),
        ));
    }



    protected function grid()
    {
        $type = request('type');

        if ($type == 'vip') {
            $grid = new Grid(new OVip());
            $this->vip($grid);
        } elseif ($type == 'badge') {
            $grid = new Grid(new Badge());
            $this->badge($grid);
        } elseif ($type == 'ware') {
            $grid = new Grid(new Ware());
            $this->ware($grid);
        } else {
            // Optional: handle invalid type
            $grid = new Grid(new OVip());
        }

        $grid->column('return', __('dedicate'))->display(function () {
            $type = request('type');
            return (new \App\Admin\Actions\DedicateSuperAdminRewardAction($this->id, $type))->render();
        });

        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->tools(function (Grid\Tools $tools) {
            $url = '/admin/super-admin-rewards-history';
            $button = '<a href="' . $url . '" class="btn btn-sm btn-success"><i class="fa fa-go"></i>&nbsp;&nbsp;' . __("admin.history") . '</a>';
            $tools->append($button);
        });
        Admin::script("
        if (window.innerWidth >= 1024) { // Example threshold for desktop screens
            $('.table-responsive').removeClass('table-responsive');
            }
        ");

        return $grid;
    }

    protected function ware($grid)
    {
        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->equal('get_type', __('get_type'))->select([
                4 => trans('purchase'),
                6 => trans('limited time purchase'),

            ]);

            // $filter->column(1 / 2, function ($filter) {

            //     $filter->equal('type', __('type'))->select(TYPE_WARE);
            // });
            $filter->column(1 / 2, function ($filter) {
                    $filter->where(function ($query) {
                        $from = request('type-ware');
                    }, __('type'), 'type-ware')->select(WARE_DEDICATE);
                });
        });

        
        $grid->column('name', __('name'));
        $grid->column('show_img', __('show_img'))->image('', 30);
        $grid->column('img2', __('show_img'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
    }

    protected function badge($grid)
    {
        $grid->model()->orderBy('priority', 'desc');

        $grid->column('image', __('image'))->display(function ($path) {
            /** @var Ware $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
    }

    protected function vip($grid)
    {
        $grid->column('level', __('level'));
        $grid->column('name', __('name'));
        $grid->column('img', __('img'))->display(function ($path) {
            /** @var OVip $this */
            $defaultImage = asset("images/image.png");
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
    }
}
