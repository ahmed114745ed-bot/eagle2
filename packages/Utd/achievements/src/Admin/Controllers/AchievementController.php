<?php

namespace Utd\Achievements\Admin\Controllers;

use App\Contracts\AchievementContract;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Utd\Achievements\Entities\Achievement;

/**
 * Achievement Admin Controller
 *
 * This controller is part of the PACKAGE (not base project).
 * It provides full admin CRUD for achievements.
 */
class AchievementController extends AdminController
{
    protected $title = 'Achievements';

    /**
     * Index - Grid view
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('Manage achievements')
            ->body($this->grid());
    }

    /**
     * Build grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid(new Achievement());

        $grid->column('id', 'ID')->sortable();
        $grid->column('type', 'Type')->badge([
            'recharge_target' => 'success',
            'room_target' => 'info',
            'gift_target' => 'warning',
        ]);
        $grid->column('created_at', 'Created')->sortable();

        $grid->actions(function ($actions) {
            $actions->add(new \Utd\Achievements\Admin\Actions\ViewLevels());
        });

        $grid->filter(function ($filter) {
            $filter->like('type', 'Type');
        });

        return $grid;
    }

    /**
     * Create form
     */
    public function create(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('Create new achievement')
            ->body($this->form());
    }

    /**
     * Edit form
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title)
            ->description('Edit achievement')
            ->body($this->form()->edit($id));
    }

    /**
     * Build form
     */
    protected function form(): Form
    {
        $form = new Form(new Achievement());

        $form->select('type', 'Type')->options([
            'recharge_target' => 'Recharge Target',
            'room_target' => 'Room Target',
            'gift_target' => 'Gift Target',
        ])->required();

        $form->select('target_type', 'Target Type')->options([
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'total' => 'Total',
        ])->required();

        return $form;
    }

    /**
     * Show detail
     */
    protected function detail($id): Show
    {
        $show = new Show(Achievement::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('type', 'Type');
        $show->field('target_type', 'Target Type');
        $show->field('created_at', 'Created');
        $show->field('updated_at', 'Updated');

        return $show;
    }
}
