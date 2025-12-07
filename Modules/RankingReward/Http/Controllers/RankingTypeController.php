<?php

namespace Modules\RankingReward\Http\Controllers;

use App\Admin\Controllers\MainController;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\RankingReward\Entities\RankingType;

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
        return new Form(new RankingType(), function (Form $form) {

            $form->tab('Types', function (Form $form) {
                $form->select('type', 'Type')
                    ->options([
                        'wealth' => 'Wealth',
                        'charm'  => 'Charm',
                        'game'   => 'Game',
                        'room'   => 'Room',
                        'agency' => 'Agency',
                    ])
                    ->rules('required|in:wealth,charm,game,room,agency');
            });

            $form->tab('Schedule', function (Form $form) {
                $form->select('schedule', 'Schedule')
                    ->options([
                        'daily'   => 'Daily',
                        'weekly'  => 'Weekly',
                        'monthly' => 'Monthly',
                    ])
                    ->rules('required|in:daily,weekly,monthly');
            });

        });
    }
}
