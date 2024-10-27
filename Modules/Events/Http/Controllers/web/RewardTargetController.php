<?php

namespace Modules\Events\Http\Controllers\web;

use App\Models\Vip;
use App\Models\OVip;
use App\Models\Ware;
use App\Models\Emoji;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Admin;

use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;

use App\Admin\Controllers\MainController;
use App\Services\AppFeatureService;
use Modules\Events\Entities\ChargeTargetEvent;
use Modules\Events\Entities\RewardTarget;
use Encore\Admin\Controllers\HasResourceActions;

class RewardTargetController extends MainController
{
    use HasResourceActions;
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("target_events");
    }
    public function index(Content $content)
    {
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->body($this->grid());
    }
    public function create(Content $content)
    {
        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    public function update($id)
    {
        $id = request()->route('id');
        return $this->form()->update($id);
    }

    public function edit($id, Content $content)
    {
        $id = request()->route('id');
        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($this->form()->edit($id));
    }

    public function show($id, Content $content)
    {
        return $content
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
            ->body($this->detail($id));
    }
    protected function grid()
    {

        $charge_event_id = request('charge_event_id');
        $target = ChargeTargetEvent::query()->find($charge_event_id);
        $grid = new Grid(new RewardTarget());
        $grid->column('created_at')->hide();
        $grid->model()->where("charge_event_id",$charge_event_id);
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('gifts'))->display(function (){
            if ($this->type == "ware"){
                return @$this->ware->name;
            }elseif ($this->type == "vip"){
                return @$this->vip->name;
            }elseif ($this->type == "coins"){
                return @$this->target;
            }elseif ($this->type == "achievement"){
                $value = getDriverUrl() . '/'. @$this->target;
                return "<img src='$value' width='80' height='80'>";
            }

        });
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware->show_img ;
            } elseif ($this->type == 'vip') {
                $vips = OVip::find($this->target);
                $path = $vips->img;
            } elseif ($this->type == 'achievement') {
                $path = $this->target;
            } else {
                $path = 'https://storage.googleapis.com/eagle-t/cion.png';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('expire', __('expire'));
        $grid->column('created_at', __('Created at'));

        $grid->tools(function (Grid\Tools $tools) use ($target){
            $url = url('admin/target-events');
            $customButtonHTML = <<<HTML
                     <div style="display: contents; align-items: center;">
                        <a href="{$url}" class="btn btn-sm btn-info" style="margin-right: 10px;">
                            <i class="fa fa-arrow-left"></i> الرجوع إلى targets
                        </a>
                        <label style="margin: 0;">هدايه الخاصه ب : {$target->value} </label>
                    </div>
                HTML;
            $tools->append($customButtonHTML);
        });

        $grid->actions (function ($actions){
            $actions->disableView();
        });

        return $grid;
    }

    protected function form()
    {
        $form = new Form(new RewardTarget());
        $form->hidden('charge_event_id')->value(request('charge_event_id'));
        $form->select('type', trans('type'))->options(["ware" => __('ware'),"vip" => __('vip'), "coins" => __('coins'),"achievement" => __('achievement')])
            ->when("ware" ,function () use ($form){
                $form->select('target1', trans('wares'))->options(function (){
                    $ops = [0=>''];
                    $wares = Ware::query()->select(['id','name', 'type'])->whereIn('type',[4,5,6])->get();
                    foreach ($wares as  $ware){
                        $ops[$ware->id]=$ware->name.'_'.$ware->id;

                        if ($ware->type == 4) {
                            $ops[$ware->id] .='_' .'bubble';
                        } elseif ($ware->type == 5) {
                            $ops[$ware->id] .= '_' .'intro';
                        } elseif ($ware->type == 6) {
                            $ops[$ware->id] .= '_' .'frame';
                        }
                    }
                    return $ops;
                });
            })
        ->when("vip",function () use ($form){
            $form->select('target2', trans('vips'))->options(function (){
                $vips = OVip::query()->select('id','name')->get();
                foreach ($vips as  $vip){
                    $ops[$vip->id]=$vip->name;
                }
                return $ops;
            });
        })
         ->when("coins",function () use ($form){
            $form->number("target3",__("coins"));
        })->when("achievement",function () use ($form){
            $form->image("target4", __('image'))->name(function ($file) {
                return now()->timestamp.'.'.$file->guessExtension();
            })->disk('gcs');
        });
        $form->number('expire', __('expire'));
        return $form;
    }
}
