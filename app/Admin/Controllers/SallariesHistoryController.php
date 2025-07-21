<?php

namespace App\Admin\Controllers;

use App\Models\Page;
use App\Http\Controllers\Controller;
use App\Models\SalaryTrx;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class SallariesHistoryController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'salary-history';
    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return parent::index($content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid()));
    }


    protected function grid()
    {
        $grid = new Grid(new SalaryTrx);
        $grid->model()->where("type",\request("type"))->orderByDesc('id');
        $grid->id(__('Id'));
        if(\request("type") == 0){

            $grid->column('name', __('Name'))
                ->display(function ($name) {

                    $user = $this->user;
                    if (!$user) return '';

                    $uid = $user->original_uuid;


                    $special =  $user->uuid_v3;

                    $path = @$user->profile?->avatar;
                    $defaultImage = asset("images/businessman-icon.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

                    //                $senderLevel = @$this->total_sender_level;
                    //                $receivedLevel = @$this->total_received_level;

                    $receiver_img = @$user->getImageReceiverOrSender('receiver_id', 1)?->img ?? '';
                    $receiverImg = getImagePath($receiver_img) ?? $defaultImage;

                    $sender_img = @$user->getImageReceiverOrSender('sender_id', 2)?->img ?? '';
                    $senderImg = getImagePath($sender_img) ?? $defaultImage;

                    $charger_img = @$user->getTotalChargeLevel($user->total_charge_level)?->img ?? '';
                    $chargerImg = getImagePath($charger_img) ??'';

                    // Check if the image exists
                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }
                    $image = handleShowImageWithTypes($user->id, $url, 50, 50);

                    return "
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            $image
                            <div>
                                <strong>$name</strong><br>
                                <span style='font-size: smaller;'>UID: $uid</span><br>
                                <span style='font-size: smaller;'>special: $special</span><br>
                                " . (!empty($receiverImg) ? "<img src='$receiverImg' style='width: 20px; height: 20px; border-radius: 50%;'>" : "") . "
                                " . (!empty($senderImg) ? "<img src='$senderImg' style='width: 20px; height: 20px; border-radius: 50%;'>" : "") . "
                                " . (!empty($chargerImg) ? "<img src='$chargerImg' style='width: 20px; height: 20px; border-radius: 50%;'>" : "") . "
                            </div>
                        </div>
                        ";
                });
        }else{
            $grid->column('name', __('Agency'))->display(function ($name) {
                $agency = $this->agency;
                if(!$agency) return '';

                $cacheKey = "agency_image_{$agency->id}";
                $image = Cache::remember($cacheKey, 3600, function () {
                    $path = @$this->img;
                    $defaultImage = asset("images/icon-agency.jpg");
                    $url = getImagePath($path) ?? $defaultImage;

                    if (!isImageExists($url)) {
                        $url = $defaultImage;
                    }

                    return handleShowImageWithTypes($this->id, $url, 40, 40, 0);
                });

                $profileUrl = route('admin.agency.profile', ['id' => $agency->id]);

                return "<a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                        <div style='display: flex; align-items: center; gap: 10px;'>
                            {$image}
                            <div style='display: flex; flex-direction: column;'>
                                <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                                <span style='font-size: smaller;'>ID: {$agency->id}</span>
                            </div>
                        </div>
                    </a>";
            });
        }
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->amount()->display(function ($num) {
            if($num > 0){
                return "<span class='text-primary '>$num</span>";
            }else{
                $num *= -1;
                return "<span class='text-danger '>$num</span>";
            }
        });
        $grid->disableExport();
        $grid->disableCreateButton();


        return $grid;
    }


}
