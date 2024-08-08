<?php

namespace Modules\Achievement\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;


use Encore\Admin\Layout\Content;

use App\Admin\Controllers\MainController;
use Modules\Achievement\Entities\UserAchievementLevel;



class UserAchievementLevelController extends MainController
{
/**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'userAchievementLevel';
    public $permission_name = 'user_achievement_level';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
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

        // $grid->column('gift_achievement_id', __('Gift achievement id'));
        // $grid->column('unique_value', __('Unique value'));
        // $grid->column('end_at', __('End at'));
        // $grid->column('is_enable', __('Is enable'));
       // $grid->column('is_enable',trans ('enable'))->states (Common::getSwitchStates ());
        $states = [
            'off'=>['value'=>0,'text'=>'no','color'=>'danger'],
            'on'=>['value'=>1,'text'=>'yes','color'=>'success'],
        ];
        $grid->column('is_enable')->switch($states);

        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(UserAchievementLevel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('achievement_level_id', __('Achievement level id'));
        $show->field('user_id', __('User id'));
        $show->field('gift_achievement_id', __('Gift achievement id'));
        $show->field('unique_value', __('Unique value'));
        $show->field('end_at', __('End at'));
        $show->field('is_enable', __('Is enable'));
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
        $form = new Form(new UserAchievementLevel());

        // $form->select('user_id', __('user'))->options('/api/search/users2')->ajax('/api/search/users2', 'id', 'name');
        // $form->select('achievement', __('Achievement'))->options(Achievement::where('type','!=','gift_target')->pluck('name', 'id'));


       // $form->select('achievementLevel', __('Achievement level id'));


        // $form->number('user_id', __('User id'));
        // // $form->select('achievement_level_id', __('Achievement'))
        // ->options(\Modules\Achievement\Entities\::pluck('name', 'id'));
        // $form->number('gift_achievement_id', __('Gift achievement id'));
        $form->switch('is_enable', trans('enable'))->states (Common::getSwitchStates ());

        return $form;
    }

    public function create(Content $content)
    {

        return $content
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body(view('admin.grid.users.UserAchievementLevel'));
    }

}
