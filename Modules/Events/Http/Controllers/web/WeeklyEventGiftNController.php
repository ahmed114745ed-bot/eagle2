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
use Modules\Events\Entities\Reward;
use App\Admin\Controllers\MainController;
use App\Services\AppFeatureService;
use Encore\Admin\Controllers\HasResourceActions;
use Modules\Events\Entities\WeeklyStar;

class WeeklyEventGiftNController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'weekly_star_rewards';
    public function __construct()
    {
        $weekly_event_id = request('weekly_event_id');
        $data = WeeklyStar::find($weekly_event_id);
        if (@$data->type == "event_period") {
            (new AppFeatureService)->validateStatusEnable("period_event");
        } else {
            (new AppFeatureService)->validateStatusEnable("weekly_star");
        }
    }
    public function index(Content $content)
    {
        $url = url('/admin/weekly-events-new'); // Define your button URL

        $buttonHTML = <<<HTML
    <a href="{$url}" class="btn btn-sm btn-success" style="margin-bottom: 20px;">
        <i class="fa fa-arrow-left"></i> رجوع
    </a>
    HTML;
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
            ->breadcrumb(
                ['text' => trans('admin.eventGift')]
            )
            ->row($buttonHTML)
            ->row($this->grid1()) // First grid
            ->row($this->grid2()) // Second grid
            ->row($this->grid3()); // Third grid


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
    protected function grid1()
    {
        $type = 1;
        $weekly_event_id = request('weekly_event_id');
        $grid = new Grid(new Reward());
        $grid->column('created_at')->hide();
        $grid->model()->where("weekly_star_id", $weekly_event_id)->where("level", $type);
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
                $path = '';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('expire', __('expire'));
        $grid->column('created_at', __('Created at'));

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) {
            $url = request()->route('weekly_event_id') . "/1/create";
            $customButtonHTML = <<<HTML

                <a href="{$url}" class="btn btn-sm btn-success" style="margin-right: 10px;">
                    <i class="fa fa-plus"></i> ضيف
                </a>
                <h3 style="margin-right: 10px;">جوائز للفائز الأول</h3>

            HTML;
            $tools->append($customButtonHTML);
        });
        return $grid;
    }

    protected function grid2()
    {
        $type = 2;
        $weekly_event_id = request('weekly_event_id');
        $grid = new Grid(new Reward());
        $grid->column('created_at')->hide();
        $grid->model()->where("weekly_star_id", $weekly_event_id)->where("level", $type);
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
                $path = '';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('expire', __('expire'));
        $grid->column('created_at', __('Created at'));

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) {
            $url = request()->route('weekly_event_id') . "/2/create";
            $customButtonHTML = <<<HTML
            <a href="{$url}" class="btn btn-sm btn-success" style="margin-right: 10px;">
                <i class="fa fa-plus"></i>ضيف
            </a>
            <h3 style="margin-right: 10px;">جوائز للفائز الثاني</h3>
            HTML;
            $tools->append($customButtonHTML);
        });

        return $grid;
    }

    protected function grid3()
    {
        $type = 3;
        $weekly_event_id = request('weekly_event_id');
        $grid = new Grid(new Reward());
        $grid->column('created_at')->hide();
        $grid->model()->where("weekly_star_id", $weekly_event_id)->where("level", $type);
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
                $path = '';
            }

            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('expire', __('expire'));
        $grid->column('created_at', __('Created at'));

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) {
            $url = request()->route('weekly_event_id') . "/3/create";
            $customButtonHTML = <<<HTML
            <a href="{$url}" class="btn btn-sm btn-success" style="margin-right: 10px;">
                <i class="fa fa-plus"></i>ضيف
            </a>
            <h3 style="margin-right: 10px;"> جوائز للفائز الثالث </h3>
            HTML;
            $tools->append($customButtonHTML);
        });

        return $grid;
    }

    protected function form()
    {
        $form = new Form(new Reward());
        $form->hidden('weekly_star_id')->value(request('weekly_event_id'));
        $form->hidden('level')->value(request('level'));

        $form->select('type', trans('type'))->options(["ware" => __('ware'), "vip" => __('vip'), "coins" => __('coins'), "achievement" => __('achievement')])
            ->when("ware", function () use ($form) {
                $form->select('target1', trans('wares'))->options(function () {
                    $ops = [0 => ''];
                    $wares = Ware::query()->select(['id', 'name', 'type'])->whereIn('type', [4, 5, 6])->get();
                    foreach ($wares as  $ware) {
                        $ops[$ware->id] = $ware->name . '_' . $ware->id;

                        if ($ware->type == 4) {
                            $ops[$ware->id] .= '_' . 'bubble';
                        } elseif ($ware->type == 5) {
                            $ops[$ware->id] .= '_' . 'intro';
                        } elseif ($ware->type == 6) {
                            $ops[$ware->id] .= '_' . 'frame';
                        }
                    }
                    return $ops;
                });
            })
            ->when("vip", function () use ($form) {
                $form->select('target2', trans('vips'))->options(function () {
                    $vips = OVip::query()->select('id', 'name')->get();
                    foreach ($vips as  $vip) {
                        $ops[$vip->id] = $vip->name;
                    }
                    return $ops;
                });
            })
            ->when("coins", function () use ($form) {
                $form->number("target3", __("coins"));
            })->when("achievement", function () use ($form) {
                $form->image("target4", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->disk('gcs');
            });
        $form->number('expire', __('expire'));
        $form->saved(function (Form $form) {
            $route = url('admin/weekly-events-gift/'.request('weekly_event_id'));
            return redirect($route);
        });
        return $form;
    }
}
