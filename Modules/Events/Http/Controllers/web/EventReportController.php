<?php

namespace Modules\Events\Http\Controllers\web;

use App\Admin\Controllers\MainOldController;
use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use App\Services\AppFeatureService;
use App\Admin\Controllers\MainController;
use Modules\Events\Entities\WinnerReward;
use Modules\Events\Entities\RewardWinnerPk;

class EventReportController extends MainOldController
{
    public $permission_name = 'event_report';
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("event_report");
    }

    public function index(Content $content)
    {
        return $content
            ->title(trans('reports'))
            ->description(__(request('desc') ?: 'users'))
            ->row(function ($row) {
                $row->column(2, view('admin.grid.common.event-reports'));
                $row->column(10, $this->grid());
            });
    }

    protected function grid()
    {
        $name = request('name') ?: 'weekly_star';
        if (request("name") == "pk_event") {
            $name = "pk_event";
        }
        if (request("name") == "event_period") {
            $name = "event_period";
        }
        $grid = $name;
        $grid = $this->{$grid}();
        $grid->disableexport();
        $grid->disableActions();
        $grid->disableCreateButton();
        return $grid;
    }

    protected function weekly_star()
    {
        $grid = new Grid(new WinnerReward());
        $grid->model()->where('type', 'weekly_star')->orWhere('type', null);
        $grid->column('id', __('ID'));

            $grid->column ('winner.name',__ ('name'))->display (function ($name){
                $name =  $this->winner?->name ?? '';
                 $uid = @$this->winner?->uuid ?? 0;
                 $path = @$this->winner?->profile?->avatar;
                 $defaultImage = asset("images/businessman-icon.jpg");
                 $url = getImagePath($path) ?? $defaultImage;

                 // Check if the image exists
                 if (!isImageExists($url)) {
                     $url = $defaultImage;
                 }
                 $image = handleShowImageWithTypes($this->id, $url, 40, 40);

                 return "
                 <div style='display: flex; align-items: center; gap: 10px;'>
                     $image
                     <div>
                         <strong>$name</strong><br>
                         <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                     </div>
                 </div>
             ";

             });


        $grid->column('reward.level', __('level'));
        $grid->column('reward.type', __('type'));
        $grid->column(__('Gifts'))->display(function () {
            if ($this->reward != null) {
                $target = '';
                if ($this->reward->type == 'coins') {
                    $target = $this->reward->target;
                } elseif ($this->reward->type == 'vip') {
                    $target = $this->reward->vip->name;
                } elseif ($this->reward->type == 'ware') {
                    $target = $this->reward->ware->name;
                }
                return $target;
            }
        });
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->reward->type == 'ware') {
               // $ware = Ware::find($this->reward->target);
                $path = $this->reward->ware->img2 ?? $this->reward->ware->show_img;
            } elseif ($this->reward->type == 'vip') {
             //   $vips = OVip::find($this->reward->target);
                $path = $this->reward->vip->img;
            } elseif ($this->reward->type == 'achievement') {
                $path = $this->reward->target;
            } else {
                $path = 'cion.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column(__('return'))->display(function () {
            $options = ['user' => __('user')];
            return (new \Modules\Events\Http\Actions\EventReportAction($this->id, 'weekly'))->render();
        });

        return $grid;
    }

    protected function pk_event()
    {
        $grid = new Grid(new RewardWinnerPk());

        $grid->column('id', __('ID'));
        $grid->column ('winner.name',__ ('name'))->display (function ($name){
            $name =  $this->winner?->name ?? '';
             $uid = @$this->winner?->uuid ?? 0;
             $path = @$this->winner?->profile?->avatar;
             $defaultImage = asset("images/businessman-icon.jpg");
             $url = getImagePath($path) ?? $defaultImage;

             // Check if the image exists
             if (!isImageExists($url)) {
                 $url = $defaultImage;
             }
             $image = handleShowImageWithTypes($this->id, $url, 40, 40);

             return "
             <div style='display: flex; align-items: center; gap: 10px;'>
                 $image
                 <div>
                     <strong>$name</strong><br>
                     <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                 </div>
             </div>
         ";

         });
        $grid->column('reward.level', __('level'));
        $grid->column('reward.type', __('type'));
        $grid->column(__('gifts'))->display(function () {
            if ($this->reward != null) {
                $target = '';
                if ($this->reward->type == 'coins') {
                    $target = $this->reward->target;
                } elseif ($this->reward->type == 'vip') {
                    $target = $this->reward->vip->name;
                } elseif ($this->reward->type == 'ware') {
                    $target = $this->reward->ware->name;
                }
                return $target;
            }
        });
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->reward->type == 'ware') {
                //  $ware = Ware::find($this->reward->target);
                $path = $this->reward->ware->img2 ?? $this->reward->ware->show_img;
            } elseif ($this->reward->type == 'vip') {
                //   $vips = OVip::find($this->reward->target);
                $path = $this->reward->vip->img;
            } elseif ($this->reward->type == 'achievement') {
                $path = $this->reward->target;
            } else {
                $path = 'cion.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column(__('return'))->display(function () {
            $options = ['user' => __('user')];
            return (new \Modules\Events\Http\Actions\EventReportAction($this->id, 'pk'))->render();
        });

        return $grid;
    }

    protected function event_period()
    {
        $grid = new Grid(new WinnerReward());
        $grid->model()->where('type', 'event_period');
        $grid->column('id', __('ID'));
        $grid->column ('winner.name',__ ('name'))->display (function ($name){
            $name =  $this->winner?->name ?? '';
             $uid = @$this->winner?->uuid ?? 0;
             $path = @$this->winner?->profile?->avatar;
             $defaultImage = asset("images/businessman-icon.jpg");
             $url = getImagePath($path) ?? $defaultImage;

             // Check if the image exists
             if (!isImageExists($url)) {
                 $url = $defaultImage;
             }
             $image = handleShowImageWithTypes($this->id, $url, 40, 40);

             return "
             <div style='display: flex; align-items: center; gap: 10px;'>
                 $image
                 <div>
                     <strong>$name</strong><br>
                     <span style='color: #aaa; font-size: smaller;'>UID: $uid</span>
                 </div>
             </div>
         ";

         });
        $grid->column('reward.level', __('level'));
        $grid->column('reward.type', __('type'));
        $grid->column(__('gifts'))->display(function () {
            if ($this->reward != null) {
                $target = '';
                if ($this->reward->type == 'coins') {
                    $target = $this->reward->target;
                } elseif ($this->reward->type == 'vip') {
                    $target = $this->reward->vip->name;
                } elseif ($this->reward->type == 'ware') {
                    $target = $this->reward->ware->name;
                }
                return $target;
            }
        });
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->reward->type == 'ware') {
               // $ware = Ware::find($this->reward->target);
                $path = $this->reward->ware->img2 ?? $this->reward->ware->show_img;
            } elseif ($this->reward->type == 'vip') {
              //  $vips = OVip::find($this->reward->target);
                $path =$this->reward->vip->img;
            } elseif ($this->reward->type == 'achievement') {
                $path = $this->reward->target;
            } else {
                $path = 'cion.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column(__('return'))->display(function () {
            $options = ['user' => __('user')];
            return (new \Modules\Events\Http\Actions\EventReportAction($this->id, 'weekly'))->render();
        });

        return $grid;
    }
}
