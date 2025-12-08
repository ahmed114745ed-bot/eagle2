<?php

namespace Modules\RankingReward\Http\Controllers;

use App\Admin\Controllers\MainController;
use App\Models\Gift;
use App\Models\Ware;
use App\Selectables\Badges;
use App\Selectables\Wares;
use App\Selectables\WaresByType;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\Badge\Entities\Badge;
use Modules\RankingReward\Entities\RankingReward;
use Modules\Vip\Entities\OVip;

class RankingRewardController extends MainController
{
    public $permission_name = 'tribe-rewards';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Tribe Rewards'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__('Tribe Reward'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        $id = request()->route('id');
        return parent::edit($id, $content
            ->title(__('Tribe Reward'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__('Tribe Reward'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new RankingReward());

        $tribe_top_id = request('ranking_range_id');
        $grid->model()->where('ranking_range_id', $tribe_top_id);

        $grid->column('id', __('ID'))->sortable();
        $grid->column('target_type', __('Type'));
        $grid->column('gift_id', __('gifts'))->display(function () {
            if ($this->target_type == "ware") {
                return @$this->ware->name;
            } elseif ($this->target_type == "vip") {
                return @$this->vip->name;
            } elseif ($this->target_type == "achievement") {
                $value = getDriverUrl() . '/' . @$this->target;
                return "<img src='$value' width='80' height='80'>";
            }
        });
        $grid->column('image', __('image'))->display(function ($path) {
            if ($this->target_type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware?->show_img;
            } elseif ($this->target_type == 'vip') {
                $vips = OVip::find($this->target);
                $path = $vips?->img;
            } elseif ($this->target_type == 'achievement') {
                $path = $this?->target;
            } else {
                $path = 'coin.png';
            }
            /** @var Gift $this */
            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });
        $grid->column('expire_days', __('expire'));
        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        if (method_exists($this, 'extendGrid')) {
            $this->extendGrid($grid);
        }

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RankingReward::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('target_type', __('Target Type'));
        $show->field('target', __('target'));
        $show->field('expire_days', __('expire'));
        $show->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        $show->column('updated_at', __('Updated At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });

        return $show;
    }

    protected function form()
    {
        $form = new Form(new RankingReward());
        $this->disableFormTools($form);

        $form->hidden('ranking_range_id')->value(request('ranking_range_id'));

        $form->select('target_type', trans('type'))->options(["ware" => __('ware'), "badge" => __('badge'), "vip" => __('vip'), "coins" => __('coins'), "achievement" => __('achievement')])
            ->when("ware", function () use ($form) {
                $this->addWareField($form);
                $form->number('expire_days', __('expire'))->default(1);
            })
            ->when("badge", function () use ($form) {
                $this->addBadgeField($form);
                $form->number('expire_days', __('expire'))->default(1);
            })
            ->when("vip", function () use ($form) {
                $form->select('target2', trans('vips'))->options(function () {
                    $vips = OVip::query()->select('id', 'name')->get();
                    foreach ($vips as  $vip) {
                        $ops[$vip->id] = $vip->name;
                    }
                    return $ops;
                });
                $form->number('expire_days', __('expire'))->default(1);
            })
            ->when("coins", function () use ($form) {
                $form->number("target3", __("coins"));
            })->when("achievement", function () use ($form) {
                $form->image("target4", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->disk('gcs');
                $form->number('expire_days', __('expire'))->default(1);
            })->rules('required');

        $form->saved(function (Form $form) {
            $route = url('admin/weekly-events-gift/' . request('weekly_event_id'));
            return redirect($route);
        });
        return $form;
    }

    public function store()
    {
        $form = $this->form();

        $form->saved(function (Form $form) {
            $tribe_top_id = $form->model()->tribe_top_id;
            admin_toastr(__('Created successfully'));
            return redirect()->to('admin/tribe_rewards/' . $tribe_top_id);
        });

        return $form->store();
    }

    public function update($id)
    {
        $id = request()->route('id');
        $form = $this->form()->edit($id);

        $form->saved(function (Form $form) {
            $tribe_top_id = $form->model()->tribe_top_id;
            admin_toastr(__('Updated successfully'));
            return redirect()->to('admin/tribe_rewards/' . $tribe_top_id);
        });

        return $form->update($id);
    }

    public function destroy($id)
    {
        $reward = RankingReward::findOrFail($id);
        $tribe_top_id = $reward->tribe_top_id;
        $reward->delete();

        admin_toastr(__('Deleted successfully'));

        return [
            'status' => true,
            'message' => __('Deleted successfully'),
            'redirect' => admin_url('tribe_rewards?tribe_top_id=' . $tribe_top_id),
        ];
    }

    protected function addWareField(Form $form)
    {
        $prefix = 'wares';
        $form->belongsTo('target1', WaresByType::class, __('Ware'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'target1')
                ->select('id', __('wares'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $ware = Ware::find($id);
                    return $ware ? [$ware->id => "{$ware->name}_{$ware->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => admin_url('wares-by-id')
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

            $this->addWareJs();
        });
    }

    protected function addBadgeField(Form $form)
    {
        $prefix = 'badges';
        $form->belongsTo('target5', Badges::class, __('Badges'), function ($form) use ($prefix) {
            $form->setElementName($prefix . 'target5')
                ->select('id', __('badges'))
                ->options(function ($id) {
                    if (!$id) return [];
                    $ware = Badge::find($id);
                    return $ware ? [$ware->id => "{$ware->name}_{$ware->id}"] : [];
                })
                ->attribute([
                    'data-image-select' => 1,
                    'data-load-url' => admin_url('wares-by-id')
                ]);

            $form->html('<div id="ware-image-preview" style="margin-top:10px;"></div>');

            $this->addWareJs();
        });
    }
}
