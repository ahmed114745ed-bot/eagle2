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
        $grid->column("description",__("description"));
        $grid->column('image', __('Img'))->image('', 30, 30);
        $grid->column("price",__("price"));
        $grid->column("type", __("type"))->display(function () {
            return $this->type;
        });
        $grid->column("relations_number",__("relations_number"));

        $grid->column('الاجرائات')->display(function () {
            if ($this->type === 'solution') {
                return '';
            } else {
                
                $url = url('admin/cp-levels/' . $this->id);
                $button = "<a href='{$url}' class='btn btn-sm btn-info'>المستويات (levels)</a>";
                return $button;
            }
        });
        // $grid->column('الاجرائات')->display(function (){
        //     $url1 = url('admin/cp-levels/' . $this->id);
        //     $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>المستويات (levels)</a>";
        //     return $button1;
        // });
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
        $form->textarea('description', __('description'));
        $form->image('image', __('Img'));
        $form->number('price', __('price'));
        // $form->number('relations_number', __('relations_number'))->default(0)->min(0);
        $form->switch('relations_number', __('relations_number'))->default(0);

        $form->select('type', __('Type'))->options([
            'bro' => __('bro'),  
            'friend' => __('friend'), 
            'lovely' => __('lovely'), 
            'solution' => __('solution'), 
        ])->default(0); // Set the default type to "Friend"        $form->number("relations_number",__("relations_number"))->default(0);
        return $form;
    }
}
