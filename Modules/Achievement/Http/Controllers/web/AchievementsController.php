<?php

namespace Modules\Achievement\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Controllers\MainController;
use Modules\Achievement\Entities\Achievement;
use Modules\Achievement\Enums\AchievementType;

class AchievementsController extends MainController
{
    public $permission_name = 'achievement';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Achievement());

        $grid->column('id', __('Id'));
        // $grid->column('type', __('Type'));

        $grid->column('type', __('Type'))->display(function ($value) {
            $prefix = request()->route()->getPrefix();
            $baseUrl = ($prefix === '/preview/admin') ? url('preview/admin/gift-achievements') : url('admin/gift-achievements');
            
            $button = '<a href="' . $baseUrl . '?achievement_id=' . $this->getKey() . '" class="btn btn-xs btn-primary"> اضف هدايا مستخدمين </a>';
            $button2 = ($value === 'gift_target') ? $button : null;
            
            return $value . '<br>' . $button2;
            
        });

        // $grid->column('valid_image', __('Valid image'));
        // $grid->column('invalid_image', __('Invalid image'));
        $grid->column('valid_image', __('Valid image'))->display(function ($value) {
            $value = getDriverUrl() . '/' . $value;
            return "<img src='$value' width='80' height='80'>";
        });

        $grid->column('invalid_image', __('Invalid image'))->display(function ($value) {
            $value = getDriverUrl() . '/' . $value;
            return "<img src='$value' width='80' height='80'>";
        });
        // $grid->column('description', __('description'));

        $grid->column(__('redirect_button'))->display(function ($value) {

            $prefix = request()->route()->getPrefix();
            $baseUrl = ($prefix === '/preview/admin') ? url('preview/admin/achievement-levels') : url('admin/achievement-levels');
            
            $button = '<a href="' . $baseUrl . '?achievement_id=' . $this->getKey() . '" class="btn btn-xs btn-primary">اضافة انواع</a>';
            
            return $button;
        });

        // $grid->column('type', __('Type'));

        // $grid->column(__('redirect_button_user_gift'))->display(function ($value) {
        //     $redirectRoute = 'User-gift';
        //     $button = '<a href="'.route($redirectRoute, ['achievement_id' => $this->getKey()]).'" class="btn btn-xs btn-primary">اضف هدايا مستخدمين</a>';
        //     return $button;
        // });ump

        // dd($button);

        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        $grid->disableCreateButton();
        //  $grid->disableActions();

        $grid->disableExport();
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
        $show = new Show(Achievement::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('valid_image', __('Valid image'));
        $show->field('invalid_image', __('Invalid image'));
        // $show->field('created_at', __('Created at'));
        // $show->field('updated_at', __('Updated at'));
        // $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Achievement());


        $form->select('type', __('Type'))->options(function () {
            $ops = [0 => ''];
            $typs = AchievementType::cases();
            foreach ($typs as  $cases) {
                $ops[$cases->value] = __($cases->value);
            }
            return $ops;
        });
        $form->image('valid_image', __('Valid image'));
        $form->image('invalid_image', __('Invalid image'));
        // $form->saving(function (Form $form) {
        //     // $data = $form->input('valid_image');
        //     // $add = Achievement::saveAchievement($data);


        //     //    redirect()->route(nameRoute('admin.achievements'));

        // });

        return $form;
    }
}
