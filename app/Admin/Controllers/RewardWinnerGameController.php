<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\MainController;
use App\Models\RewardWinnerGame;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RewardWinnerGameController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'RewardWinnerGame';


    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('Reward Winner Game'))
            ->body($this->grid()));
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('Reward Winner Game'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('Reward Winner Game'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('Reward Winner Game'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RewardWinnerGame());

        $grid->model()->orderBy('rank', 'asc');

        $grid->column('id', __('Id'))->display(function ($value) {
            return "<span class='rwg-id'>{$value}</span>";
        });

        $grid->column('rank', __('Rank'))->display(function ($value) {
            return "<span class='rwg-rank'>{$value}</span>";
        });

        $grid->column('reward_coins', __('Reward coins'))->display(function ($value) {
            $formatted = number_format($value);
            return "<div class='rwg-coins'>
                        <span class='rwg-coins-icon'>🪙</span>
                        <span class='rwg-coins-value'>{$formatted}</span>
                    </div>";
        });

        $grid->disableExport();
        $grid->disableRowSelector();

        Admin::style($this->gridStyles());

        return $grid;
    }

    /**
     * Grid CSS styles
     */
    private function gridStyles(): string
    {
        return '
            /* ── Table ── */
            .grid-table > thead > tr > th {
                background: #f8fafc !important;
                color: #475569 !important;
                font-weight: 700 !important;
                font-size: 12px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.8px !important;
                padding: 14px 16px !important;
                border-bottom: 2px solid #e2e8f0 !important;
            }
            .grid-table > tbody > tr > td {
                padding: 12px 16px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f1f5f9 !important;
            }
            .grid-table > tbody > tr:hover > td {
                background: #f0f4ff !important;
            }

            /* ── ID ── */
            .rwg-id {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 40px;
                padding: 4px 10px;
                background: #eef2ff;
                color: #4338ca;
                font-weight: 700;
                font-size: 12px;
                border-radius: 6px;
                border: 1px solid #c7d2fe;
            }

            /* ── Rank ── */
            .rwg-rank {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                font-weight: 800;
                font-size: 14px;
                border-radius: 50%;
                box-shadow: 0 2px 6px rgba(102,126,234,0.3);
            }

            /* ── Coins ── */
            .rwg-coins {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 6px 16px;
                background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                border: 1px solid #fde68a;
                border-radius: 24px;
                min-width: 100px;
                justify-content: center;
            }
            .rwg-coins-icon {
                color: #f59e0b;
                font-size: 14px;
            }
            .rwg-coins-value {
                font-weight: 800;
                font-size: 14px;
                color: #92400e;
                font-family: monospace;
                letter-spacing: 0.3px;
            }

            /* ── Action Buttons ── */
            .grid-row-actions .btn {
                border-radius: 8px !important;
                margin: 1px !important;
                padding: 4px 8px !important;
            }

            /* ── Pagination ── */
            .box-footer .pagination > li > a,
            .box-footer .pagination > li > span {
                border-radius: 8px !important;
                margin: 0 2px !important;
            }
            .box-footer .pagination > .active > a {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                border-color: transparent !important;
            }
        ';
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(RewardWinnerGame::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('rank', __('Rank'));
        $show->field('reward_coins', __('Reward coins'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RewardWinnerGame());

        $form->number('rank', __('Rank'))
            ->required()
            ->creationRules('unique:reward_winner_games,rank')
            ->updateRules('unique:reward_winner_games,rank,{{id}}');
        $form->number('reward_coins', __('Reward coins'))->required();

        return $form;
    }
}
