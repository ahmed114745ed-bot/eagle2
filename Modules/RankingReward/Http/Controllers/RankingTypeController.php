<?php

namespace Modules\RankingReward\Http\Controllers;

use App\Admin\Controllers\MainController;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\MessageBag;
use Modules\RankingReward\Entities\RankingType;
use Modules\RankingReward\Entities\RankingRange;

class RankingTypeController extends MainController
{
    public $permission_name = 'ranking-types';

    public function index(Content $content)
    {
        $type = request('type', 'wealth');
        $schedule = request('schedule', 'daily');

        return $content
            ->title('Ranking Types')
            ->row(function($row) use ($type, $schedule) {
                $row->column(12, $this->grid2($type, $schedule));

                $row->column(12, $this->grid($type, $schedule));
            });
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__('Ranking Range'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(__('Edit Ranking Range'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__('Create Ranking Range'))
            ->body($this->form()));
    }

    protected function grid2($type, $schedule)
    {
        return new Box('', view('admin.grid.users.ranking_tabs', [
            'type' => $type,
            'schedule' => $schedule
        ])->render());
    }

    protected function grid($type, $schedule)
    {
        $grid = new Grid(new RankingRange());

        $grid->model()->whereHas('rankingType', function ($query) use ($type, $schedule) {
            $query->where('type', $type)->where('schedule', $schedule);
        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('min', __('Min Rank'));
        $grid->column('max', __('Max Rank'));

        $grid->column('range', __('Range'))->display(function () {
            if ($this->max === null) {
                return "<span class='label label-info'>{$this->min}</span>";
            }
            return "<span class='label label-info'>{$this->min} - {$this->max}</span>";
        });

        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });

        if (Admin::user()->can('browse-ranking-rewards') || Admin::user()->can('*')) {
            if (!request()->filled('_export_')) {
                $grid->column(__('Procedures'))->display(function () {
                    $url = url('admin/ranking-rewards/' . $this->id);
                    $text = __('Ranking Rewards');
                    return "<a href='{$url}' class='btn btn-sm btn-info'>{$text}</a>";
                });
            }
        }

        $grid->disableCreateButton();

        $grid->tools(function ($tools) use ($type, $schedule) {
            $tools->append(
                "<a href='".admin_url("ranking-types/create?type={$type}&schedule={$schedule}")."' class='btn btn-sm btn-success'>
                    <i class='fa fa-plus'></i>&nbsp;&nbsp;".__('New')."
                </a>"
            );
        });

        if (method_exists($this, 'extendGrid')) {
            $this->extendGrid($grid);
        }

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RankingRange::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('min', __('Min Rank'));
        $show->field('max', __('Max Rank'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new RankingRange());

        $form->hidden('ranking_type_id');

        if (!$form->isEditing()) {
            $type = request('type', 'wealth');
            $schedule = request('schedule', 'daily');

            $rankingType = RankingType::firstOrCreate([
                'type' => $type,
                'schedule' => $schedule
            ]);

            $form->hidden('ranking_type_id')->value($rankingType->id);

            $existingRanges = RankingRange::where('ranking_type_id', $rankingType->id)
                ->orderBy('min')
                ->get()
                ->map(function ($r) {
                    if ($r->max === null) {
                        return "Rank {$r->min}";
                    }
                    return "{$r->min} - {$r->max}";
                })
                ->implode(', ');

            if ($existingRanges) {
                $form->html("<div class='alert alert-info'> ".__('Existing ranges:')." <strong>{$existingRanges}</strong></div>");
            }
        }

        $form->number('min', __('Min Rank'))->min(1)->required();
        $form->number('max', __('Max Rank'))->min(1)->help('Leave empty for single rank');

        $form->saving(function (Form $form) {
            $rankingTypeId = $form->ranking_type_id;
            $min = (int) $form->min;
            $max = $form->max ? (int) $form->max : null;
            $currentId = $form->model()->id;

            $effectiveMax = $max ?? $min;

            if ($max !== null && $min > $max) {
                $error = new MessageBag([
                    'min' => [__('Min rank must be less than or equal to max rank')],
                ]);
                return back()->withErrors($error)->withInput();
            }

            $existingRanges = RankingRange::where('ranking_type_id', $rankingTypeId)
                ->when($currentId, function ($query) use ($currentId) {
                    $query->where('id', '!=', $currentId);
                })
                ->get();

            foreach ($existingRanges as $range) {
                $existingMin = $range->min;
                $existingMax = $range->max ?? $range->min;

                if ($this->rangesOverlap($min, $effectiveMax, $existingMin, $existingMax)) {
                    $display = $range->max === null ? "Rank {$range->min}" : "{$range->min} - {$range->max}";
                    $error = new MessageBag([
                        'min' => [__('Range overlaps with existing: :display', ['display' => $display])],
                    ]);
                    return back()->withErrors($error)->withInput();
                }
            }
        });

        return $form;
    }

    protected function rangesOverlap($min1, $max1, $min2, $max2): bool
    {
        if ($max1 < $min2) {
            return false;
        }

        if ($min1 > $max2) {
            return false;
        }

        return true;
    }
}
