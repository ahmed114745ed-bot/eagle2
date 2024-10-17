<?php

namespace Modules\CP\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Controllers\MainController;
use Modules\Achievement\Entities\Achievement;
use Modules\Achievement\Enums\AchievementType;
use Modules\CP\Entities\CpRelation;

class CpRelationController extends MainController
{
    
    protected function grid()
    {
        $grid = new Grid(new CpRelation());

        $grid->column('id', __('Id'));
        $grid->column("title",__("title"));
        $grid->column('image', __('Img'))->image('', 30, 30);
        $grid->column("price",__("price"));

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
        $show = new Show(CpRelation::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('valid_image', __('Valid image'));
        $show->field('invalid_image', __('Invalid image'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CpRelation());
        $form->text('title', __('title'));
        $form->image('image', __('Img'));
        $form->number('price', __('price'));

        return $form;
    }
}
