<?php

namespace Utd\Achievements\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Utd\Achievements\Entities\AchievementLevel;
use Utd\Achievements\Support\AchievementHelper;

class AchievementLevelController extends AdminController
{
    protected $title = 'Achievement Levels';

    public function index(Content $content)
    {
        $achievementId = request()->route('id');

        return $content
            ->title($this->title)
            ->description("Levels for Achievement #{$achievementId}")
            ->body($this->grid($achievementId));
    }

    protected function grid($achievementId): Grid
    {
        $grid = new Grid(new AchievementLevel());

        $grid->model()->where('achievement_id', $achievementId)->orderBy('target');

        $grid->column('id', 'ID');
        $grid->column('target', 'Target')->sortable();
        $grid->column('target_type', 'Type');
        $grid->column('valid_image', 'Valid Image')->image('', 50, 50);
        $grid->column('invalid_image', 'Invalid Image')->image('', 50, 50);
        $grid->column('ar_description', 'AR Description')->limit(30);
        $grid->column('en_description', 'EN Description')->limit(30);

        $grid->disableExport();
        $grid->disableFilter();

        return $grid;
    }

    public function create(Content $content)
    {
        $achievementId = request()->route('id');

        return $content
            ->title($this->title)
            ->description('Create new level')
            ->body($this->form($achievementId));
    }

    protected function form($achievementId = null): Form
    {
        $form = new Form(new AchievementLevel());

        $form->hidden('achievement_id')->value($achievementId);

        $form->number('target', 'Target')->required()->min(0);

        $form->select('target_type', 'Target Type')->options([
            'coins' => 'Coins',
            'count' => 'Count',
            'amount' => 'Amount',
        ])->required();

        $form->image('valid_image', 'Valid Image')
            ->move('achievements')
            ->uniqueName();

        $form->image('invalid_image', 'Invalid Image')
            ->move('achievements')
            ->uniqueName();

        $form->text('ar_description', 'Arabic Description');
        $form->text('en_description', 'English Description');

        return $form;
    }
}
