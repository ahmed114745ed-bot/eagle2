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
            ->title(trans('Gift Badges'))
            ->body($this->grid()));
    }

    public function create(Content $content)
    {
        $achievementValidImage=AchievementValidImage::where('user_id',Auth::user()->id)->get();
        return parent::create($content
             ->title(trans('user-achievement-levels'))
            ->body(view('admin.grid.users.UserAchievementLevel',compact('achievementValidImage'))));
    }

    protected function grid2()
    {
        $grid = new Grid(new UserAchievementLevel());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column(1/2, function ($filter) {
                $filter->equal('user.uuid',__('uuid'));

            });
        });
        $grid->column('id', __('Id'));
        // $grid->column('achievement_level_id', __('Achievement level id'));
        // $grid->column('user_id', __('User id'));
        $grid->column('user.name', __('Users'));
        $grid->column('achievementLevel.target', __('achievement_level_target'))->display(function( $column) {
            if($this->achievement_level_id != null )
            {
              return $this->achievementLevel->target ?? 0;
            }elseif( $this->gift_achievement_id  !=null ){
                return $this->achievement->target ?? 0;
            }

            return "custom";

        });
        $grid->column('custom_image', __('custom_image'))->display(function ($value)  {
            if($value != null){
               $image = $value;
            }else{
                $image = $this->achievementLevel?->valid_image ?? $this->giftAchievement()->whereHas('gift',function($q){
                    $q->select('img');
                })->first()->gift->img ?? null;
            }
            $value = getDriverUrl() .'/'. $image;
            return "<img src='$value' width='80' height='80'>";
        });

        $states = [
            'off'=>['value'=>0,'text'=>'no','color'=>'danger'],
            'on'=>['value'=>1,'text'=>'yes','color'=>'success'],
        ];
        $grid->column('is_enable')->switch($states);


        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
        });

        return $grid;
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Achievement());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
        });
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('type', __('Type'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->add(new AchievementDedicateAction());
        });

        return $grid;
    }

}
