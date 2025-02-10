<?php

namespace Modules\Achievement\Http\Controllers\web;

use App\Admin\Actions\AchievementDedicateAction;
use App\Models\AchievementValidImage;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;


use Encore\Admin\Layout\Content;

use App\Admin\Controllers\MainController;
use Illuminate\Support\Facades\Auth;
use Modules\Achievement\Entities\Achievement;
use Modules\Achievement\Entities\AchievementLevel;
use Modules\Achievement\Entities\UserAchievementLevel;



class AchievementDedicateController extends MainController
{
/**
     * Title for current resource.
     *
     * @var string
     */
    public $permission_name = 'achievement_dedicate';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('achievement-dedicate'))
            ->body($this->grid()));
    }

    public function create(Content $content)
    {
        $achievementValidImage = AchievementValidImage::all();
        return parent::create($content
             ->title(trans('user-achievement-levels'))
            ->body(view('admin.grid.users.UserAchievementLevelDedicate',compact('achievementValidImage'))));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AchievementLevel());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
        });
        $grid->column('id', __('Id'));
        $grid->column('achievement_id', __('Achievement Id'));
        $grid->column('target', __('Target'));
        $grid->column('valid_image', __('Valid image'))->display(function($value){
            $value = getDriverUrl() .'/'. $value;
            return "<img src='$value' width='80' height='80'>";
        });

        $grid->column('invalid_image', __('Invalid image'))->display(function($value){
            $value = getDriverUrl() .'/'. $value;
            return "<img src='$value' width='80' height='80'>";
        });
        $grid->column('ar_description', __('Description Ar'));
        $grid->column('en_description', __('Description En'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->add(new AchievementDedicateAction());
        });

        return $grid;
    }

}
