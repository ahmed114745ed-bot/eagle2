<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\RoomCategory;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class RoomCategoryController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'categories';
    public $hiddenColumns = [

    ];

    public function index(Content $content)
    {
        return $content
            ->title(trans('categories'))
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
            ->title(trans('categories'))
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
            ->title(trans('categories'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->title(trans('categories'))
            ->body($this->form());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RoomCategory);

        $grid->id(__ ('ID'));
        $grid->name(trans('name'));
        $grid->column('name_en',trans ('name_en'));
        $grid->column('img',trans ('img'))->image ('',30);
        $grid->column('enable',trans ('enable'))->switch (Common::getSwitchStates ());
        $this->extendGrid ($grid);
        $grid->disableExport();

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
        $show = new Show(RoomCategory::findOrFail($id));

//        $show->id('ID');
//        $show->parent_id('parent_id');
//        $show->name('name');
//        $show->img('img');
//        $show->enable('enable');
//        $show->created_at(trans('admin.created_at'));
//        $show->updated_at(trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RoomCategory);

        $form->display(__ ('ID'));
        $form->select ('parent_id',trans ('parent'))->options (function (){
            $options = [0=>trans ('root')];
            $cats = RoomCategory::query ()->where ('id','!=',$this->id)->where ('enable',1)->where ('parent_id',0)->get ();
            foreach ($cats as $cat){
                $options[$cat->id] = $cat->name;
            }
            return $options;
        });
        $form->text('name', trans('name'))->rules ('required');
        $form->text('name_en', trans('name_en'))->rules ('required');
        $form->image('img', trans('img'))->rules ('required');
        $form->switch('enable', trans('enable'))->states (Common::getSwitchStates ());

        return $form;
    }
}
