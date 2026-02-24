<?php

namespace Utd\Tasks\Http\Controllers;

use Utd\Vip\Entities\OVip;
use App\Models\Ware;
use Utd\Tasks\Entities\TaskReward;
use Utd\Tasks\Entities\Day;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Utd\Achievements\Entities\Achievement;
use App\Support\PackageHelper;

class TaskRewardController extends AdminController
{
    protected $title = 'TaskReward';

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

    protected function grid()
    {
        $dayId = request('day_id');
        $grid = new Grid(new TaskReward());
        $grid->column('created_at')->hide();
        $grid->model()->where("day_id", $dayId);

        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('gifts'))->display(function () {
            if ($this->type == "ware") {
                return @$this->ware->name;
            } elseif ($this->type == "vip") {
                return @$this->vip->name;
            } elseif ($this->type == "coins") {
                return @$this->target;
            } elseif ($this->type == "achievement") {
                $value = getDriverUrl() . '/' . @$this->target;
                return "<img src='$value' width='80' height='80'>";
            }
        });
        $grid->column('created_at', __('Created at'));

        $grid->tools(function ($tools) use ($dayId) {
            $day = Day::find($dayId);
            $createUrl = url('admin/days');
            $tools->append('<a href="' . $createUrl . '" class="btn btn-success btn-sm">الذهاب الي قائمه الايام</a>');
            $tools->append('<div><h5 style="color:yellow">قائمه هدايا  '.$day?->title.' </h5></div>');
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(TaskReward::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('day_id', 'Day ID');
        $show->field('type', 'Type');
        $show->field('target', 'Target');
        $show->field('expire', 'Expire Date');
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    public function edit($id, Content $content)
    {
        $id = request()->route('id');
        $model = TaskReward::findOrFail($id);

        $form = $this->form()->edit($id);

        if ($model->type == 'coins') {
            $form->coins = (int) $model->target;
        }

        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($form);
    }

    protected function form()
    {
        $form = new Form(new TaskReward());
        $dayId = request('day_id');
        if ($dayId) {
            $form->hidden('day_id')->default($dayId);
        } else {
            $form->select('day_id', 'Day')
                ->options(Day::pluck('title', 'id'))
                ->required();
        }

        $typeOptions = [
            "ware" => __('ware'),
            "coins" => __('coins'),
        ];
        if (PackageHelper::isInstalled('vip')) {
            $typeOptions["vip"] = __('vip');
        }
        if (class_exists(Achievement::class)) {
            $typeOptions['achievement'] = __('achievement');
        }

        $form->select('type', trans('type'))->options($typeOptions)
            ->when("ware", function () use ($form) {
                $form->select('target', trans('wares'))->options(function () {
                    $ops = [0 => ''];
                    $wares = Ware::query()->select(['id', 'name', 'type'])->whereIn('type', [4, 5, 6])->get();
                    foreach ($wares as $ware) {
                        $ops[$ware->id] = $ware->name . '_' . $ware->id;
                        if ($ware->type == 4) {
                            $ops[$ware->id] .= '_bubble';
                        } elseif ($ware->type == 5) {
                            $ops[$ware->id] .= '_intro';
                        } elseif ($ware->type == 6) {
                            $ops[$ware->id] .= '_frame';
                        }
                    }
                    return $ops;
                });
            })
            ->when("vip", function () use ($form) {
                $form->select('target', trans('vips'))->options(function () {
                    $ops = [];
                    if (PackageHelper::isInstalled('vip')) {
                        $vips = OVip::query()->select('id', 'name')->get();
                        foreach ($vips as $vip) {
                            $ops[$vip->id] = $vip->name;
                        }
                    }
                    return $ops;
                });
            })
            ->when("coins", function () use ($form) {
                $form->number("coins", __("coins"));
            })
            ->when("achievement", function () use ($form) {
                $form->image("achievement", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->disk('gcs');
            });

        $form->number('expire', __('expire'));

        $form->saving(function (Form $form) {
            if ($form->type == 'ware') {
                $ware = Ware::find($form->target);
                if ($ware) {
                    if ($ware->type == 4) {
                        $form->sub_type = 'bubble';
                    } elseif ($ware->type == 5) {
                        $form->sub_type = 'intro';
                    } elseif ($ware->type == 6) {
                        $form->sub_type = 'frame';
                    }
                }
            } elseif ($form->type == 'vip') {
                // No special handling for vip
            } elseif ($form->type == 'coins') {
                $form->target = $form->coins;
            } elseif ($form->type == 'achievement') {
                $form->target = $form->achievement;
            }
        });

        return $form;
    }
}
