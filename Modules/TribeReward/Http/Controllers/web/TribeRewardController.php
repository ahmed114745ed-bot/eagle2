<?php

namespace Modules\TribeReward\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use App\Models\Gift;
use App\Models\OVip;
use App\Models\Ware;
use App\Selectables\Wares;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\TribeReward\Entities\TribeReward;

class TribeRewardController extends MainController
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
        $grid = new Grid(new TribeReward());

        $tribe_top_id = request('tribe_top_id');
        $grid->model()->where('tribe_top_id', $tribe_top_id);

        $grid->column('id', __('ID'))->sortable();
        $grid->column('type', __('Type'));
        $grid->column('gift_id', __('gifts'))->display(function () {
            if ($this->target_type == "ware") {
                return @$this->ware->name;
            } elseif ($this->target_type == "vip") {
                return @$this->vip->name;
            } elseif ($this->target_type == "coins") {
                return @$this->target;
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
        $grid->column('quantity', __('Quantity'));
        $grid->column('expire_days', __('expire'));
        $grid->column('created_at', __('Created At'));

        if (method_exists($this, 'extendGrid')) {
            $this->extendGrid($grid);
        }

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(TribeReward::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('type', __('Type'));
        $show->field('target_type', __('Target Type'));
        $show->field('target', __('target'));
        $show->field('quantity', __('Quantity'));
        $show->field('expire_days', __('expire'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new TribeReward());

        $tribe_top_id = request('tribe_top_id');
        $form->hidden('tribe_top_id')->default($tribe_top_id);

        $form->select('type', __('Type'))->options([
            'agency_reward' => __('Agency Reward'),
            'share_rewards' => __('Share Rewards'),
        ])->required();

        $form->select('target_type', trans('Target Type'))->options(["ware" => __('ware'), "vip" => __('vip'), "coins" => __('coins'), "achievement" => __('achievement')])
            ->when("ware", function () use ($form) {
                $form->belongsTo('target1', Wares::class, trans('wares'))->rules('required');
            })
            ->when("vip", function () use ($form) {
                $form->select('target2', trans('vips'))->options(function () {
                    $vips = OVip::query()->select('id', 'name')->get();
                    foreach ($vips as  $vip) {
                        $ops[$vip->id] = $vip->name;
                    }
                    return $ops;
                })->rules('required');
            })
            ->when("coins", function () use ($form) {
                $form->number("target3", __("coins"))->rules('required');
            })->when("achievement", function () use ($form) {
                $form->image("target4", __('image'))->name(function ($file) {
                    return now()->timestamp . '.' . $file->guessExtension();
                })->disk('gcs');
            });

        $form->number('quantity', __('Quantity'))->required();
        $form->number('expire_days', __('expire'))->required();

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
        $reward = TribeReward::findOrFail($id);
        $tribe_top_id = $reward->tribe_top_id;
        $reward->delete();

        admin_toastr(__('Deleted successfully'));

        return [
            'status' => true,
            'message' => __('Deleted successfully'),
            'redirect' => admin_url('tribe_rewards?tribe_top_id=' . $tribe_top_id),
        ];
    }
}
