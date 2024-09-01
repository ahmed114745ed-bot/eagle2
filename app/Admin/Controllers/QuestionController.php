<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Question;
use Encore\Admin\Controllers\AdminController;

class QuestionController extends AdminController
{
    protected $title = 'Question';

    protected function grid()
    {
        $grid = new Grid(new Question());

        $grid->column('id', __('Id'));
        $grid->column('question', __('question'));
        $grid->column('answer', __('answer'));
        $grid->column('status', __('status'))->switch (Common::getSwitchStates ());;
        return $grid;
    }


    protected function detail($id)
    {
        $show = new Show(Question::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('question', __('question'));
        $show->field('answer', __('answer'));
        return $show;
    }

   
    protected function form()
    {
        $form = new Form(new Question());

        $form->text('question', __('question'))->required();
        $form->textarea('answer', __('answer'))->required();
        $form->switch('status', __('status'))->states (Common::getSwitchStates ());

        return $form;
    }
}