<?php

namespace Modules\Milestones\Http\Controllers\web;

use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Admin;
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
       
        return $content
            ->header(__('Milestone Rewards'))
            ->description(__('Rewards for milestone'))
            ->body($this->grid($milestoneId));
    }

    public function create(Content $content)
    {
        return $content
            ->header(__('Create Reward'))
            ->description(__('Add a new reward to milestone'))
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
        $grid->column('type', __('Type'))->label();

        $grid->column('reward_id', __('Reward'))->display(function () {
            if ($this->type === "ware") {
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

        $grid->column('image', __('Image'))->display(function () {
            if ($this->type === "coins") {
                return  $this->reward;
            }elseif ($this->type === "ware") {
                $path = $this->rewardable?->img2 ?? $this->rewardable?->show_img;
            } elseif ($this->type === "vip") {
                $path = $this->rewardable?->img;
            } elseif ($this->type === "achievement") {
                $path = $this->reward;
            } elseif ($this->type === "badge") {
                $path = $this->rewardable?->icon;
            } else {
                $path = 'coin.png';
            }

            $url = getImagePath($path);
            return handleShowImageWithTypes($this->id, $url, 50, 50);
        });

        $grid->column('expire', __('Expire'))->display(fn($expire) => $expire ?: '-');

        $grid->tools(function (Grid\Tools $tools) {
            $url = url('admin/milestones');
            $back = __('Back');
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

        $form->hidden('milestone_id')->value(request('milestone_id'));

        $form->select('type', __('Type'))->options([
            "coins"        => __('Coins'),
            "ware"        => __('Ware'),
            "vip"         => __('Vip'),
            "achievement" => __('Achievement'),
            "badge"       => __('Badge'),
        ])->when("ware", function (Form $form) {
            $form->belongsTo('rewardable_id', Wares::class, trans('Wares'))->rules('required');
        })->when("vip", function (Form $form) {
            $form->select('rewardable_id', __('Vip'))
                ->options(OVip::pluck('name', 'id'))
                ->rules('required');
        })->when("achievement", function (Form $form) {
            $form->image("reward", __('Image'))->name(function ($file) {
                return now()->timestamp . '.' . $file->guessExtension();
            });
        })->when("badge", function (Form $form) {
            $form->select('rewardable_id', __('Badge'))
                ->options(Badge::pluck('name', 'id'))
                ->rules('required');
        })->when("coins", function (Form $form) {
            $form->number("reward", __('Coins'))->rules('required|integer|min:1');
        });

        $form->number('expire', __('Expire'))->default(1);

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
                    $form->rewardable_type = \Modules\Achievement\Entities\Achievement::class;
                    $form->model()->rewardable_type = \Modules\Achievement\Entities\Achievement::class;
                    break;
                case 'coins':
                    $form->rewardable_id = 0;
                    $form->rewardable_type = \App\Models\User::class;
                    $form->model()->rewardable_type =\App\Models\User::class;
                    $form->reward = (int) $form->reward; 
                    $form->model()->reward =$form->reward;
        
                    break;    
            }
        });

        

        return $form;
    }

    protected function detail($id)
    {
        $show = new Show(MilestoneReward::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('type', __('Type'));
        $show->field('rewardable_type', __('Rewardable Type'));
        $show->field('expire', __('Expire'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }
}
