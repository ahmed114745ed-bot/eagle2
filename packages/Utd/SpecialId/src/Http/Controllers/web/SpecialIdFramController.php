<?php

namespace Utd\SpecialId\Http\Controllers\web;

use App\Admin\Controllers\MainController;
use App\Models\Emoji;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Utd\SpecialId\Entities\SpecialIdFram;

class SpecialIdFramController extends MainController
{
    use HasResourceActions;

    public $permission_name = 'special-frame';

    public function index(Content $content)
    {
        return $content
            ->title(trans('frames'))
            ->body($this->grid());
    }

    public function show($id, Content $content)
    {
        return $content
            ->title(trans('frames'))
            ->body($this->detail($id));
    }

    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('frames'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('frames'))
            ->body($this->form());
    }

    protected function grid()
    {
        $grid = new Grid(new SpecialIdFram);

        $grid->id(__('ID'));
        $grid->title(__('name'));
        $grid->column('image', __('img'))->image('', 30);
        $grid->column('color', __('color'));

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Emoji::findOrFail($id));

        $show->id('ID');
        $show->title('name');
        $show->color('color');
        $show->image('image');

        return $show;
    }

    protected function form()
    {
        $form = new Form(new SpecialIdFram());
        $form->text('title', __('name'));
        $form->image('image', __('img'));
        $form->color('color', __('color'));

        return $form;
    }
}
