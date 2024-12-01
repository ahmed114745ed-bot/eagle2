<?php

namespace App\Admin\Controllers;

use App\Models\Code;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CodeController extends MainController
{
    use HasResourceActions;
    

    public $permission_name = 'charge';
    public function index(Content $content)
    {
        return $content
            ->title(trans('code'))
            ->body($this->grid());
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
        return $content
            ->title(trans('code'))
            ->body($this->detail($id));
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
        return $content
            ->title(trans('code'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->title(trans('code'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Code);

        $grid->id(__ ('ID'));
        $grid->column('phone',trans ('phone'));
        $grid->column('code',trans ('code'));
        $this->extendGrid ($grid);
        $grid->disableExport();
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
        $show = new Show(Code::findOrFail($id));

        $show->id(__ ('ID'));
        $show->phone(__('phone'));
        $show->code(__('code'));

        $this->extendShow ($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Code);

        $form->display(__ ('ID'));
        $form->text('phone', __ ('phone'));
        $form->text('code', __ ('code'));

        return $form;
    }
}
