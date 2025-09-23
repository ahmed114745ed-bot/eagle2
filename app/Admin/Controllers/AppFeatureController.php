<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\AppFeature;
use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;

class AppFeatureController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'AppFeature';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('AppFeature'))
            ->body($this->grid()));
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('AppFeature'))
            ->body($this->detail($id)));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('AppFeature'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('AppFeature'))
            ->body($this->form()));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AppFeature());

        $grid->column('id', __('Id'));
        $grid->column('name_ar', __('name'));
        $grid->column('name', __('name_en'));
        $grid->column('slug', __('slug'));
        $grid->column('status', __('status'));
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
        $show = new Show(AppFeature::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('name_ar', __('Name ar'));
        $show->field('slug', __('Slug'));
        $show->field('status', __('Status'));
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
        $form = new Form(new AppFeature());


        $form->text('name_ar', __('name '));
        $form->text('name', __('name_en'));
        $form->text('slug', __('Slug'));
        $state = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];

        $form->switch('status', __("status"))->states($state);

        return $form;
    }
}
