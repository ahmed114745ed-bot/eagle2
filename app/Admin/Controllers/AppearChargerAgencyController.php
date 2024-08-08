<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;

class AppearChargerAgencyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->filter(function (Grid\Filter $filter) {
            $filter->expand();
            $filter->column('1/2', function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->where('name', $input)
                        ->orWhere('uuid', $input)->orWhere('phone', $input);
                }, __('User'))->placeholder(__('Search by name , UUID , phone'));
            });
        });
        $grid->model()->whereIn('type_user', [4, 3]);
        $grid->column('id', __('Id'));
        $grid->column('uuid', __('Uuid'));
        $grid->column('name', __('Name'));
        $grid->column('profile.avatar', __('image'))->image('', 50);
        $grid->column('phone', __('Phone'));
        $grid->column('agency_id', __('agency_id'));
        $grid->column('appear_charger_agency', __('Appear charger agency'))->switch(Common::getSwitchStates());
        $grid->disableCreateButton();
        $grid->disableActions();

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('phone', __('Phone'));
        $show->field('uuid', __('Uuid'));
        $show->field('appear_charger_agency', __('Appear charger agency'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('name', __('Name'));
        $form->mobile('phone', __('Phone'));
        $form->text('uuid', __('Uuid'));
        $form->switch('appear_charger_agency', __('Appear charger agency'));

        return $form;
    }
}
