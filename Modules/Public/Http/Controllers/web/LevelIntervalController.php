<?php

namespace Modules\Public\Http\Controllers\web;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Modules\Public\Entities\LevelInterval;
use Encore\Admin\Controllers\AdminController;



class LevelIntervalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'levelInterval';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LevelInterval());

        $grid->column('id', __('Id'));
        $grid->column('name', __('name'));
        $grid->column('type', __('type'))->display(function ($value) {
            return  $this->type == 3?"room":($this->type == 1? "receiver":"sender");
        });
        $grid->column('min', __('min'));
        $grid->column('max', __('max'));
        $grid->column('الاجرائات')->display(function () {
            // توليد الروابط
            $url1 = url('admin/reward_level_interval/' . $this->id);

            // إنشاء أزرار HTML
            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>   هداية </a>";

            // دمج الأزرار في سلسلة واحدة وإرجاعها
            return $button1 ;
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
        $show = new Show(LevelInterval::findOrFail($id));

        $show->field('id', __('id'));
        $show->field('name', __('name'));
        $show->field('min', __('min'));
        $show->field('max', __('max'));
       

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LevelInterval());

        $form->text('name', __('name'));
        $form->select('type', trans('type'))->options (
            [
                1=>trans ('receiver'),
                 2=>trans ('sender'),
                 3=>trans ('room'),
            ]
         )->default (2);
        $form->number('min', __('min'));
        $form->number('max', __('max'));

        return $form;
    }
}
