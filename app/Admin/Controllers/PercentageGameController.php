<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Selectables\Users;
use App\Models\PercentageGame;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;

class PercentageGameController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'PercentageGame';
    public $permission_name = 'game-settings';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('percentage game'))
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
            ->title(trans('percentage game'))
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
            ->title(trans('percentage game'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('percentage game'))
            ->body($this->form()));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PercentageGame());

        $grid->column('id', __('id'));
        $grid->column('title', __('title'));

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
        $show = new Show(PercentageGame::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
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
        $form = new Form(new PercentageGame());

        $form->text('title', __('title'));
        $form->number('percentage_game', __('percentage game'))->default(2);
        $form->belongsToMany('users', Users::class, trans('users'))
            ->rules('required|array');

        return $form;
    }
    
}
