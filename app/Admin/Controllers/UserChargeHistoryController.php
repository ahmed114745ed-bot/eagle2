<?php

namespace App\Admin\Controllers;

use Carbon\Carbon;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\AdminController;

class UserChargeHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'user charge history';

    public function indexCharge(Content $content, $user_id)
    {
        if (!request()->has('scope')) {
            return redirect()->to(url()->current() . '?scope=charge-to');
        }
        return $content
            ->title(trans('user charge history'))
            ->row(function ($row) use ($user_id) {
                $row->column(12, $this->gridTabs()); // <-- Tab buttons
            })
            ->row(function ($row) use ($user_id) {
                $row->column(12, $this->customGrid($user_id)); // <-- Main grid
            });
    }
    protected function gridTabs()
    {
        $scope = request('scope', 'charge-to');

        $html = '
    <style>
        .tab-buttons {
            margin-bottom: 15px;
        }
        .tab-buttons .tab-button {
            color: black !important;
            margin-right: 10px;
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f7f7f7;
        }
        .tab-buttons .tab-button.active {
            background-color: #007bff;
            color: white !important;
            border-color: #007bff;
        }
    </style>
    <div class="tab-buttons">
        <a href="?scope=charge-to" class="tab-button btn-dash ' . ($scope === 'charge-to' ? 'active' : '') . '">' . __('Charged to') . '</a>
        <a href="?scope=charge-from" class="tab-button btn-agency ' . ($scope === 'charge-from' ? 'active' : '') . '">' . __('Charged from') . '</a>
    </div>';

        return new \Encore\Admin\Widgets\Box(__(), $html);
    }


    protected function customGrid($userId)
    {
        $grid = new Grid(new Charge());
        $grid->disableRowSelector();
        $scope = request('scope');
        if ($scope === 'charge-to') {
            $grid->model()->where('charger_id', $userId)->where('charger_type', 'user')->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        } else {
            $grid->model()->where('user_id', $userId)->where('user_type', 'user')->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)->where('charger_type', '!=', 'dash');
        }



        // Define columns
        $grid->column('id', __('ID'));
        if ($scope === 'charge-to') {
            $grid->column('admin.name', __('receiver'))->display(function () {
                $sender = Common::getReceiverInfo($this);
                if (empty($sender['name']) && empty($sender['uuid'])) {
                    return "
                <div style='display: flex; align-items: center; gap: 10px;'>

                            <span style=' cursor: pointer;'>Unknown </span>

                </div>
            ";
                }

                $name = $sender['name'];
                $uuid = $sender['uuid'];
                $path = $sender['image'];
                $showUrl = $sender['url'];
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                // Check if the image exists
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
                // $image = handleShowImageWithTypes($this->id, $url, 40, 40);


                $imageStyle = $this->charger_type == 'agency'
                    ? 'width: 40px; height: 40px; object-fit: cover; border-radius: 0;'     // rectangle
                    : 'width: 40px; height: 40px; object-fit: cover; border-radius: 50%;';
                $image = "<img src='{$url}' alt='User Image' style='{$imageStyle}'>";

                return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                            <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uuid</span>
                    </div>
                </div>
            ";
            });
        } else {

            $grid->column('charger_id', __("charger"))->display(function () {

                $sender = Common::getChargerInfo($this);
                if (empty($sender['name']) && empty($sender['uuid'])) {
                    return "
                <div style='display: flex; align-items: center; gap: 10px;'>

                            <span style=' cursor: pointer;'>Unknown </span>

                </div>
            ";
                }

                $name = $sender['name'];
                $uuid = $sender['uuid'];
                $path = $sender['image'];
                $showUrl = $sender['url'];
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                // Check if the image exists
                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }
                // $image = handleShowImageWithTypes($this->id, $url, 40, 40);


                $imageStyle = $this->charger_type == 'agency'
                    ? 'width: 40px; height: 40px; object-fit: cover; border-radius: 0;'     // rectangle
                    : 'width: 40px; height: 40px; object-fit: cover; border-radius: 50%;';
                $image = "<img src='{$url}' alt='User Image' style='{$imageStyle}'>";

                return "
                <div style='display: flex; align-items: center; gap: 10px;'>
                    $image
                    <div>
                        <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                            <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                        </a>
                        <span style='color: #aaa; font-size: smaller;'>UUID: $uuid</span>
                    </div>
                </div>
            ";
            });
        }


        $grid->column('amount_and_usd', __('coins & USD'))->display(function () {
            $coin = number_format($this->amount); // Assuming 'amount' is the coin value
            $usd = $this->usd;

            $coinIcon = asset('images/coin.jpg');
            $usdIcon = asset('images/dollar.jpg');

            return "
                    <div style='display: flex; flex-direction: column; gap: 5px;'>
                        <div style='display: flex; align-items: center; gap: 5px;'>
                            <span>{$coin}</span>
                            <img src='{$coinIcon}' alt='Coin' width='20' height='20'>
                        </div>
                        <div style='display: flex; align-items: center; gap: 5px;'>
                            <span>{$usd}</span>
                            <img src='{$usdIcon}' alt='USD' width='20' height='20'>
                        </div>
                    </div>
                ";
        });


        $grid->column('created_at', __('charge date'));
        $grid->tools(function (Grid\Tools $tools) {
            $url = '/admin/reset-salary';
            $button = '<a href="' . $url . '" class="btn btn-sm btn-success"><i class="fa fa-go"></i>&nbsp;&nbsp;' . __("back") . '</a>';
            $tools->append($button);
        });
        // Disable unnecessary buttons
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableActions();

        return $grid;
    }
}
