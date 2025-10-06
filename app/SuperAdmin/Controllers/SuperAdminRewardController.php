<?php

namespace App\SuperAdmin\Controllers;

use App\Models\Ware;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Modules\Vip\Entities\OVip;
use Encore\Admin\Facades\Admin;
use App\Models\SuperAdminReward;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;

class SuperAdminRewardController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */

    public function index(Content $content)
    {
        session(['last_ware_type' => request()->get('type', 'vip')]);
        return $content
            ->title(trans('Rewards Center'))
            ->row(function (Row $row) {
                $row->column(12, $this->tabsComponent());
            })
            ->row(function (Row $row) {
                $row->column(12, $this->grid());
            });
    }





    protected function grid()
    {
        $type = request()->get('type', 'vip');
        $grid = new Grid(new SuperAdminReward());
        $grid->model()->where('type',  $type);
        $authId = Admin::user()->id;
        $grid->column('id', __('Id'));
        $grid->model()->where('super_admin_id', $authId);
        $grid->column('gift_id', __('gifts'))->display(function () {
            if ($this->type == "ware") {
                return @$this->ware->name ?? '';
            } elseif ($this->type == "vip") {
                return @$this->vip->name ?? '';
            } elseif ($this->type == "badge") {
                return @$this->badge->name ?? '';
            } elseif ($this->type == "coins") {
                return @$this->target;
            } elseif ($this->type == "achievement") {
                $value = getDriverUrl() . '/' . @$this->target;
                return "<img src='$value' width='80' height='80'>";
            }
        });
        if (!request()->filled('_export_')) {
            $grid->column('image', __('image'))->display(function ($path) {
                if ($this->type == 'ware') {
                    $ware = Ware::find($this->target);
                    $path = $ware->img2 ?? ($ware->show_img ?? "");
                } elseif ($this->type == 'vip') {
                    $vips = OVip::find($this->target);
                    $path = $vips->img ?? '';
                } elseif ($this->type == 'badge') {
                    // $vips = Badge::find($this->target);
                    $path = @$this->badge->image ?? '';
                } elseif ($this->type == 'achievement') {
                    $path = $this->target;
                } else {
                    $path = 'coin.png';
                }

                /** @var Gift $this */
                $url = getImagePath($path);
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
        }
        $grid->column('no_reward', __('No reward'))->display(function () {
            return $this->no_reward - $this->gave_reward_no;
        });


        $grid->column('return', __('dedicate'))->display(function () {

            return (new \App\Admin\Actions\SuperAdminDedicateRewardAction($this->id))->render();
        });

        $grid->disableActions();
        $grid->disableRowSelector();
        $grid->disableExport();
         $grid->disableCreateButton();
        return $grid;
    }


    private function tabsComponent()
    {
        $content = new Row();

        // Define your type mapping
        $typeMap = SELECTED_USED_WARE;

        $types =  ['vip', 'ware',  'badge', /** 'achievement'*/];
        $currentType = request()->get('type', 'vip');

        $box = new Box(content: view('admin.grid.Form.rewardTabs', [
            'types' => $types,
            'currentType' => $currentType
        ]));

        $content->column(12, $box);

        return $content;
    }
}
