<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Interest;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Lang;
use App\Admin\Controllers\MainController;

class InterestsController extends  MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('interests'))
            ->body($this->grid()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Interest());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('img', __('Img'))->image(width: 100, height: 100);
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Interest::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('img', __('Img'));
        // $show->field('created_at', __('Created at'));
        // $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Interest());

        $form->text('name', __('Name'));
        $form->image('img', __('Img'));

        return $form;
    }
}
