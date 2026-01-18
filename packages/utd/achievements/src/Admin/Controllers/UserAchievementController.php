<?php

namespace Utd\Achievements\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Utd\Achievements\Entities\UserAchievementLevel;

class UserAchievementController extends AdminController
{
    protected $title = 'User Achievements';

    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('View user achievements')
            ->body($this->grid());
    }

    protected function grid(): Grid
    {
        $grid = new Grid(new UserAchievementLevel());

        $grid->model()->with(['user', 'achievementLevel.achievement'])->orderByDesc('id');

        $grid->column('id', 'ID')->sortable();

        // Dynamic user column
        $grid->column('user.uuid', 'User UUID');

        $grid->column('achievementLevel.achievement.type', 'Achievement Type');
        $grid->column('achievementLevel.target', 'Level Target');

        $grid->column('is_enable', 'Enabled')->switch([
            'on' => ['value' => 1, 'text' => 'Yes', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => 'No', 'color' => 'danger'],
        ]);

        $grid->column('picked', 'Picked')->bool();
        $grid->column('created_at', 'Achieved At')->sortable();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->where(function ($query) {
                $userModel = config('achievements.models.user');
                $user = $userModel::where('uuid', $this->input)->first();
                if ($user) {
                    $query->where('user_id', $user->id);
                }
            }, 'User UUID');

            $filter->equal('is_enable', 'Enabled')->select([
                1 => 'Yes',
                0 => 'No',
            ]);
        });

        $grid->disableCreateButton();
        $grid->disableExport();

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

        return $grid;
    }
}
