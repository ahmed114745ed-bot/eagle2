<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\FamilyLevel;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Services\AppFeatureService;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

class FamilyLevelController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'family-level';
    public $hiddenColumns = [

    ];
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("families");
    }

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('family-level'))
            ->body($this->grid()));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(trans('family-level'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('family-level'))
            ->body($this->form()));
    }
    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(trans('family-level'))
            ->body($this->detail($id)));
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FamilyLevel);

        $grid->id( __ ('ID'));
        $grid->column('name',__ ('name'));
        $grid->column('img',__ ('img'))->image ('',30);
        $grid->column('exp',__ ('exp'));
        $grid->column('members',__ ('members'));
        $grid->column('admins',__ ('admins'));
//        $grid->type('type');
//        $grid->created_at(trans('admin.created_at'));
//        $grid->updated_at(trans('admin.updated_at'));

        $grid->actions (function ($actions){
            $actions->disableView();
        });
        $grid->disableExport();
        $this->extendGrid ($grid);
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
        $show = new Show(FamilyLevel::findOrFail($id));

//        $show->id('ID');
//        $show->name('name');
//        $show->img('img');
//        $show->exp('exp');
//        $show->type('type');
//        $show->created_at(trans('admin.created_at'));
//        $show->updated_at(trans('admin.updated_at'));
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
        $form = new Form(new FamilyLevel);

        $form->display( __ ('ID'));
        $form->text('name', __('name'));
        $form->image('img', __('img'));
        $form->number('exp', __('exp'));
        $form->number('members', __('members'));
        $form->number('admins', __('admins'));
//        $form->text('type', 'type');
//        $form->display(trans('admin.created_at'));
//        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
