<?php

namespace App\Admin\Controllers;

use App\Models\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\AdminUser;
use Illuminate\Support\Str;
use App\Enums\ConfigCategory;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Services\AppFeatureService;
Use Encore\Admin\Admin;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;

class ConfigController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'config';
    public $hiddenColumns = [

    ];

    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("config");
    }

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        if (! \Encore\Admin\Facades\Admin ::user()->can( '*')){
            Permission::check('browse-'.$this->permission_name);
        }
        return $content
            ->header(trans('admin.index'))
            ->description(trans('admin.description'))
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
            ->header(trans('admin.detail'))
            ->description(trans('admin.description'))
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
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
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
            ->header(trans('admin.create'))
            ->description(trans('admin.description'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Config);
        if (request("name") != null) {
            $admin_user=AdminUser::find(auth()->user()->id);
            $admin_user->time_zone=request("name");
            $admin_user->save();
            request()->merge(['name' => null]);
            return $this->grid();
        }
        
        $grid->model()->where('is_hidden',0);
        $grid->id('ID');
        $grid->name(trans('name'));
        $grid->column('value',trans('value'))->display(function($text) {
            return Str::limit($text, 50, '...');
        })->editable ();
        $grid->desc(trans ('description'));
        Admin::style('.dropdown-toggle {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ccc;
        }
        .dropdown-menu {
            min-width: 200px;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .dropdown-item {
            color: #333;
                padding: 0 5px;
                display: block;
        }');
        Admin::js('https://cdn.bootcss.com/vue/2.6.10/vue.min.js');


        $grid->tools(function ($tools) {
            // استخدم append() لإضافة الزر بجانب زر الإضافة
            $tools->append('<div class="dropdown" style="margin-top:10px;">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                ' . trans('select_time_zone') . '
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="?name=EET">Egypt</a><br>
                <a class="dropdown-item" href="?name=AST">SAD</a><br>
                <a class="dropdown-item" href="?name=CET">Morocco</a><br>
            </div>
        </div>');
        });
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
        $show = new Show(Config::findOrFail($id));

        $show->id('ID');
        $show->name(trans('name'));
        $show->value(trans('value'));
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
        $form = new Form(new Config);

        $form->display('ID');
        $form->text('name', trans('name'));
        $form->text('value', trans('value'));
        $form->textarea ('desc',trans ('description'));
        $form->select('category', trans('category'))
        ->options(
            array_combine(
                translate( ConfigCategory::getOptions()),
                array_map(fn($value) => ucfirst(str_replace('_', ' ', $value)), translate(ConfigCategory::getOptions()))
            )
        )
        ->required();

        return $form;
    }

}
