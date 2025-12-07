<?php

namespace Modules\RankingReward\Http\Controllers;

use App\Admin\Controllers\MainController;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Modules\RankingReward\Entities\RankingType;
use Modules\RankingReward\Entities\RankingRange;

class RankingTypeController extends MainController
{
    public $permission_name = 'ranking-types';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Ranking Types'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__('Ranking Types'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(__('Edit Ranking Types'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__('Create Ranking Types'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new RankingType());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('level', __('level'));

        $grid->column('created_at', __('Created At'))->display(function ($value) {
            return Carbon::parse($value)->format('Y-m-d');
        });
        if (Admin::user()->can('browse-room-boom-rewards') || Admin::user()->can('*')) {
            if (!request()->filled('_export_')) {
                $grid->column(__('Procedures'))->display(function () {
                    $url = url('admin/room_boom_rewards/' . $this->id);
                    $text = __('Room Boom Rewards');
                    return "<a href='{$url}' class='btn btn-sm btn-info'>{$text}</a>";
                });
            }
        }
        if (method_exists($this, 'extendGrid')) {
            $this->extendGrid($grid);
        }

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(RankingType::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('level', __('level'));
        $show->field('min_target', __('target'));
        $show->field('target', __('target'));
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
        $form = new Form(new RankingType());

        // REMOVE type & schedule from the form entirely.
        // DO NOT PUT HIDDEN FIELDS IN THE FORM.
        // Leave only your tabs and ranges.

        $types = ['wealth','charm','game','room','agency'];
        $schedules = ['daily','weekly','monthly'];

                foreach ($types as $type) {
                    $activeTab = ($form->model()->type ?? 'wealth') == $type ? 'active' : '';
                    $form->tab(ucfirst($type), function(Form $form) use ($type, $schedules) {

                        $html = "<ul class='nav nav-tabs schedule-tabs' id='schedule-tabs-$type'>";
                        foreach ($schedules as $sch) {
                            $active = $sch === 'daily' ? 'active' : '';
                            $html .= "<li class='$active'><a data-toggle='tab' href='#{$type}-{$sch}' data-schedule='{$sch}'>".ucfirst($sch)."</a></li>";
                        }
                        $html .= "</ul><div class='tab-content' style='margin-top:20px;'>";

                        foreach ($schedules as $sch) {
                            $active = $sch === 'daily' ? 'active' : '';
                            $minValue = '';
                            $maxValue = '';
                            if ($form->model()->type == $type && $form->model()->schedule == $sch) {
                                $range = $form->model()->ranges->first();
                                $minValue = $range ? $range->min : '';
                                $maxValue = $range ? $range->max : '';
                            }
                            $html .= "
                            <div class='tab-pane $active' id='{$type}-{$sch}'>
                                <div>
                                    <label>Min</label>
                                    <input type='number' class='form-control' name='ranges[$type][$sch][min]' value='$minValue'>
                                </div>
                                <div>
                                    <label>Max</label>
                                    <input type='number' class='form-control' name='ranges[$type][$sch][max]' value='$maxValue'>
                                </div>
                            </div>";
                        }

                        $html .= "</div>";

                        $form->html($html);

                    })->activeIf($activeTab);
                }

        // JS: store active TYPE + SCHEDULE in hidden JS variables
        Admin::script("
        window.selectedType = 'wealth';
        window.selectedSchedule = 'daily';

        $('.nav.nav-tabs > li > a').on('shown.bs.tab', function(e){
            window.selectedType = $(e.target).text().trim().toLowerCase();
        });

        $('.schedule-tabs a').on('shown.bs.tab', function(e){
            window.selectedSchedule = $(e.target).data('schedule');
        });

        // Submit the form with hidden inputs for type and schedule
        $('form').on('submit', function(){
            $(this).append('<input type=\"hidden\" name=\"selected_type\" value=\"' + window.selectedType + '\">');
            $(this).append('<input type=\"hidden\" name=\"selected_schedule\" value=\"' + window.selectedSchedule + '\">');
        });
    ");

        // Here is the MAGIC FIX
        // This runs AFTER the form is submitted,
        // and BEFORE data is saved.
        $form->saving(function(Form $form){
            $form->model()->type = request()->input('selected_type') ?: 'wealth';
            $form->model()->schedule = request()->input('selected_schedule') ?: 'daily';
        });

        // Handle ranges after save
        $form->saved(function(Form $form){
            $ranges = request()->input('ranges');
            if ($ranges && isset($ranges[$form->model()->type][$form->model()->schedule])) {
                $rangeData = $ranges[$form->model()->type][$form->model()->schedule];
                RankingRange::updateOrCreate(
                    ['ranking_type_id' => $form->model()->id],
                    ['min' => $rangeData['min'], 'max' => $rangeData['max']]
                );
            }
        });

        return $form;
    }
}
