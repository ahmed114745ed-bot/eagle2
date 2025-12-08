<?php

namespace Modules\RankingReward\Http\Controllers;

use App\Admin\Controllers\MainController;
use App\Models\Ware;
use App\Selectables\Wares;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\RankingReward\Entities\RankingRange;
use Modules\RankingReward\Entities\RankingReward;
use Modules\Vip\Entities\OVip;

class RankingRewardController extends MainController
{
    public $permission_name = 'ranking-rewards';

    public function index(Content $content)
    {
        $rankingRangeId = request('ranking_range_id');
        $range = RankingRange::with('rankingType')->find($rankingRangeId);

        $title = 'Ranking Rewards';

        return parent::index($content
            ->title(__($title))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__('Ranking Reward'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(__('Edit Ranking Reward'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__('Create Ranking Reward'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new RankingReward());

        $rankingRangeId = request('ranking_range_id');
        $grid->model()->where('ranking_range_id', $rankingRangeId);

        $grid->column('id', __('ID'))->sortable();

        $grid->column('target_type', __('Type'))->display(function ($value) {
            $labels = [
                'ware' => '<span class="label label-info">Ware</span>',
                'vip' => '<span class="label label-warning">VIP</span>',
                'achievement' => '<span class="label label-success">Achievement</span>',
                'coin' => '<span class="label label-primary">Coin</span>',
            ];
            return $labels[$value] ?? $value;
        });

        $grid->column('target', __('Reward'))->display(function () {
            if ($this->target_type == "ware") {
                $ware = Ware::find($this->target);
                return $ware?->name ?? '-';
            } elseif ($this->target_type == "vip") {
                $vip = OVip::find($this->target);
                return $vip?->name ?? '-';
            } elseif ($this->target_type == "achievement") {
                $value = getDriverUrl() . '/' . $this->target;
                return "<img src='$value' width='50' height='50'>";
            } elseif ($this->target_type == "coin") {
                return "Coins: " . $this->target;
            }
            return '-';
        });

        $grid->column('image', __('Image'))->display(function () {
            $path = null;
            if ($this->target_type == 'ware') {
                $ware = Ware::find($this->target);
                $path = $ware->img2 ?? $ware?->show_img;
            } elseif ($this->target_type == 'vip') {
                $vip = OVip::find($this->target);
                $path = $vip?->img;
            } elseif ($this->target_type == 'achievement') {
                $path = $this->target;
            } elseif ($this->target_type == 'coin') {
                $path = 'coin.png';
            }

            if ($path) {
                $url = getImagePath($path);
                return "<img src='{$url}' width='50' height='50'>";
            }
            return '-';
        });

        $grid->column('expire_days', __('Expire Days'))->display(function ($value) {
            return $value ? $value . ' days' : '-';
        });

        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });

        // Custom create button
        $grid->disableCreateButton();
        $grid->tools(function ($tools) use ($rankingRangeId) {
            $tools->append(
                "<a href='".admin_url("ranking-rewards/create?ranking_range_id={$rankingRangeId}")."' class='btn btn-sm btn-success'>
                    <i class='fa fa-plus'></i>&nbsp;&nbsp;New
                </a>"
            );

            // Back button
            $range = RankingRange::with('rankingType')->find($rankingRangeId);
            if ($range) {
                $type = $range->rankingType->type;
                $schedule = $range->rankingType->schedule;
                $tools->append(
                    "<a href='".admin_url("ranking-types?type={$type}&schedule={$schedule}")."' class='btn btn-sm btn-default' style='margin-left:10px;'>
                        <i class='fa fa-arrow-left'></i>&nbsp;&nbsp;Back
                    </a>"
                );
            }
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RankingReward::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('target_type', __('Type'));
        $show->field('target', __('Target'));
        $show->field('expire_days', __('Expire Days'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Reward());
        $this->disableFormTools($form);

        $form->hidden('weekly_star_id')->value(request('weekly_event_id'));
        $form->hidden('level')->value(request('level'));

        $form->select('type', trans('type'))->options(["ware" => __('ware'), "badge" => __('badge'), "vip" => __('vip'), "coins" => __('coins'), "achievement" => __('achievement')])
            ->when("ware", function () use ($form) {
                $this->addWareField($form);
                $form->number('expire', __('expire'))->default(1);
            })
            ->when("badge", function () use ($form) {
                $this->addBadgeField($form);
                $form->number('expire', __('expire'))->default(1);
            })
            ->when("vip", function () use ($form) {
                $form->select('target2', trans('vips'))->options(function () {
                    $vips = OVip::query()->select('id', 'name')->get();
                    foreach ($vips as  $vip) {
                        $ops[$vip->id] = $vip->name;
                    }
                    return $ops;
                });
                $form->number('expire', __('expire'))->default(1);
            })
            ->when("coins", function () use ($form) {
                $form->number("target3", __("coins"));
            })->when("achievement", function () use ($form) {
                $form->image("target4", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->disk('gcs');
                $form->number('expire', __('expire'))->default(1);
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
            $rankingRangeId = $form->model()->ranking_range_id;
            admin_toastr(__('Created successfully'));
            return redirect()->to(admin_url('ranking-rewards?ranking_range_id=' . $rankingRangeId));
        });

        return $form->store();
    }

    public function update($id)
    {
        $form = $this->form()->edit($id);

        $form->saved(function (Form $form) {
            $rankingRangeId = $form->model()->ranking_range_id;
            admin_toastr(__('Updated successfully'));
            return redirect()->to(admin_url('ranking-rewards?ranking_range_id=' . $rankingRangeId));
        });

        return $form->update($id);
    }

    public function destroy($id)
    {
        $reward = RankingReward::findOrFail($id);
        $rankingRangeId = $reward->ranking_range_id;
        $reward->delete();

        admin_toastr(__('Deleted successfully'));

        return [
            'status' => true,
            'message' => __('Deleted successfully'),
            'redirect' => admin_url('ranking-rewards?ranking_range_id=' . $rankingRangeId),
        ];
    }
}
