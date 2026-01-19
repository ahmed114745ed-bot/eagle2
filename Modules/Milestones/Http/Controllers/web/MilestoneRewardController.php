<?php

namespace Modules\Milestones\Http\Controllers\web;

use App\Selectables\Badges;
use App\Selectables\OVips;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Admin;
use Modules\Milestones\Entities\Milestone;
use Modules\Milestones\Entities\MilestoneReward;
use Modules\Achievement\Entities\Achievement;
use Modules\Badge\Entities\Badge;
use Modules\Vip\Entities\OVip;
use App\Models\Ware;
use App\Selectables\Wares;

class MilestoneRewardController
{
    use HasResourceActions;

    public function index(Content $content, $milestoneId = null)
    {
        $milestone = Milestone::find($milestoneId);
        return $content
            ->header(__('Milestone rewards') . '-:-' . $milestone->name)
            ->description(__('id') . '-:-' . $milestone->id)
            ->body($this->grid($milestoneId));
    }

    public function create(Content $content)
    {
        return $content
            ->header(__('create'))
            // ->description(__('Add a new reward to milestone'))
            ->body($this->form());
    }

    public function show($id, Content $content)
    {

        return $content
            ->header(__('Detail'))
            ->description(__('Reward details'))
            ->body($this->detail($id));
    }

    public function edit($id, Content $content)
    {
        $id = request('id');
        return $content
            ->header(__('Edit Reward'))
            ->description(__('Edit milestone reward'))
            ->body($this->form()->edit($id));
    }

    protected function grid($milestoneId)
    {

        $grid = new Grid(new MilestoneReward());

        if ($milestoneId) {
            $grid->model()->where('milestone_id', $milestoneId);
        }

        $grid->column('id', __('ID'))->sortable();
        if (!request()->filled('_export_')) {
            $grid->column('type', __('Type'))->label();
        } else {
            $grid->column('type', __('Type'));
        }

        $grid->column('reward_id', __('Rewards'))->display(function () {
            if ($this->type === "coins") {
                return $this?->reward ?? "-";
            } elseif ($this->type === "ware") {
                return $this->rewardable?->name ?? "-";
            } elseif ($this->type === "vip") {
                return $this->rewardable?->name ?? "-";
            } elseif ($this->type === "achievement") {
                return $this->rewardable?->title ?? "-";
            } elseif ($this->type === "badge") {
                return $this->rewardable?->title ?? "-";
            }
            return "-";
        });
        if (!request()->filled('_export_')) {
            $grid->column('image', __('Image'))->display(function () {
                if ($this->type === "coins") {
                    $path = 'coin.png';
                } elseif ($this->type === "ware") {
                    $path = $this->rewardable?->img2 ?? $this->rewardable?->show_img;
                } elseif ($this->type === "vip") {
                    $path = $this->rewardable?->img;
                } elseif ($this->type === "achievement") {
                    $path = $this->reward;
                } elseif ($this->type === "badge") {
                    $path = $this->rewardable?->image;
                } else {
                    $path = 'coin.png';
                }

                $url = getImagePath($path);
                return handleShowImageWithTypes($this->id, $url, 50, 50);
            });
        }
        $grid->column('expire', __('Expire'))->display(fn($expire) => $expire ?: '-');

        $grid->tools(function (Grid\Tools $tools) {
            $url = url('admin/milestones');
            $back = __('back');
            $customButtonHTML = <<<HTML
                <a href="{$url}" class="btn btn-sm btn-info" style="margin-right: 10px;">
                    <i class="fa fa-arrow-left"></i> {$back}
                </a>
            HTML;
            $tools->append($customButtonHTML);
        });



        Admin::script("
            if (window.innerWidth >= 1024) {
                $('.table-responsive').removeClass('table-responsive');
            }
        ");

        return $grid;
    }

    protected function form()
    {
        $form = new Form(new MilestoneReward());
       // $form->html('<div class="custom-full-width">');
        $form->hidden('milestone_id')->value(request('milestone_id'));

        $form->select('type', __('Type'))->options([
            "coins"        => __('Coins'),
            "ware"         => __('Wares'),
            "vip"          => __('vip'),
            "achievement"  => __('Achievement'),
            "badge"        => __('Badge'),
        ])
            ->when("ware", function (Form $form) {


                $form->html('<div class="full-column-width">');
                $form->belongsTo('rewardable_id', Wares::class, trans('Wares'))->rules('required')->column(10000000);
                $form->html('</div>');
                $form->number('expire', __('Expire'))->default(1)->help(__('admin.lifetime_help'));
            })
            ->when("vip", function (Form $form) {
                $form->belongsTo('rewardable_id2', OVips::class, trans('vip'));
                $form->number('expire', __('Expire'))->default(1)->help(__('admin.lifetime_help'));
            })
            ->when("achievement", function (Form $form) {

                $form->image("reward1", __('Image'))
                    ->name(fn($file) => now()->timestamp . '.' . $file->guessExtension())
                    ->disk('gcs');

                $form->number('expire', __('Expire'))->default(1)->help(__('admin.lifetime_help'));
            })
            ->when("badge", function () use ($form) {
                $this->addBadgeField($form);
                $form->number('expire', __('Expire'))->default(1)->help(__('admin.lifetime_help'));
            })
            ->when("coins", function (Form $form) {
                $form->number("reward2", __('Coins'))->rules('required|integer|min:1');
            });


        Admin::style('
              .form-horizontal .fields-group > .col-md-12 > .form-group .input-group {
                    display: table !important;
                    width: 50% !important;
                }
        ');
        $form->saving(function (Form $form) {
            switch ($form->type) {
                case 'ware':
                    $form->rewardable_type = \App\Models\Ware::class;
                    $form->model()->rewardable_type = \App\Models\Ware::class;
                    break;

                case 'vip':
                    $form->rewardable_type = \Modules\Vip\Entities\OVip::class;
                    $form->model()->rewardable_type = \Modules\Vip\Entities\OVip::class;
                    break;

                case 'badge':
                    $form->rewardable_type = \Modules\Badge\Entities\Badge::class;
                    $form->model()->rewardable_type = \Modules\Badge\Entities\Badge::class;
                    break;

                case 'achievement':
                    $form->rewardable_id = 0;
                    $form->model()->rewardable_id = 0;
                    $form->rewardable_type = \Modules\Achievement\Entities\Achievement::class;
                    $form->model()->rewardable_type = \Modules\Achievement\Entities\Achievement::class;


                    break;

                case 'coins':
                    $form->rewardable_id = 0;
                    $form->model()->rewardable_id = 0;
                    $form->model()->rewardable_type = \App\Models\User::class;
                    $form->reward = (int) $form->reward2;
                    $form->model()->reward = $form->reward2;
                    break;
            }
        });
        $form->saved(function (Form $form) {

            $route = url('admin/milestone-rewards/' . request('milestone_id'));
            return redirect($route);
        });
        return $form;
    }


    protected function detail($id)
    {
        $show = new Show(MilestoneReward::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('type', __('Type'));
        // $show->field('rewardable_type', __('Rewardable_type'));
        $show->field('expire', __('Expire'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    protected function addBadgeField(Form $form)
    {
        $prefix = 'badges';
        $form->belongsTo('rewardable_id3', Badges::class, __('Badges'), function ($form) use ($prefix) {
            $form
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
