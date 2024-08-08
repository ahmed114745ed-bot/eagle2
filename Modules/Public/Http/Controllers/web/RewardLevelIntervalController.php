<?php

namespace Modules\Public\Http\Controllers\web;


use App\Models\OVip;
use App\Models\Ware;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Controllers\HasResourceActions;
use Modules\Public\Entities\RewardLevelInterval;

class RewardLevelIntervalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'RewardLevelInterval';
    use HasResourceActions;

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

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $level_interval = request('level_interval_id');
        $grid = new Grid(new RewardLevelInterval());
        $grid->model()->where('level_interval_id',$level_interval);
        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('Gifts'))->display(function (){
            if ($this->type == "ware"){
                return @$this->ware->name;
            }elseif ($this->type == "vip"){
                return @$this->vip->name;
            }elseif ($this->type == "coins"){
                return @$this->target;
            }elseif ($this->type == "achievement"){
                $value = getDriverUrl() . '/' . @$this->target;
                return "<img src='$value' width='80' height='80'>";
            }

        });

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
        $show = new Show(RewardLevelInterval::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('target', __('Target'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RewardLevelInterval());

        $form->hidden('level_interval_id')->value(request('level_interval_id'));
        $form->select('type', trans('type'))->options(["ware" => __('ware'),"vip" => __('vip'), "coins" => __('coins'),"achievement" => __('achievement')])
            ->when("ware" ,function () use ($form){
                $form->select('target1', trans('wares'))->options(function (){
                    $ops = [0=>''];
                    $wares = Ware::query()->select(['id','name', 'type'])->whereIn('type',[4,5,6])->get();
                    foreach ($wares as  $ware){
                        $ops[$ware->id]=$ware->name.'_'.$ware->id;

                        if ($ware->type == 4) {
                            $ops[$ware->id] .='_' .'frame';
                        } elseif ($ware->type == 5) {
                            $ops[$ware->id] .= '_' .'bubble';
                        } elseif ($ware->type == 6) {
                            $ops[$ware->id] .= '_' .'intro';
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
            });
        });
        return $form;
    }
    }
