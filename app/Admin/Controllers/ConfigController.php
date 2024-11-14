<?php

namespace App\Admin\Controllers;

use App\Models\OVip;
use App\Models\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Enums\ConfigType;
use App\Models\AdminUser;
use Illuminate\Support\Str;
use App\Enums\ConfigCategory;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
Use Encore\Admin\Admin;
use App\Services\AppFeatureService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
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
        $form = $this->form()->edit($id);
        if ($form->model()->type == 'integer') {
            $form->valueInteger = $form->model()->value; 
        }elseif ($form->model()->type== 'select') {
            $form->valueSelect = $form->model()->value; 
        }
        return $content
            ->header(trans('admin.edit'))
            ->description(trans('admin.description'))
            ->body($form);
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

        $grid->column('desc',trans ('description'))->display(function($desc) {
            return Lang::has('dashboard.' . $desc) ? __('dashboard.' . $desc) : $desc;
        });
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

        // if ($form->isEditing()) {
        //     if ($form->model()->type == 'integer') {
        //         $form->valueInteger = $form->model()->value; 
        //     }elseif ($form->model()->type== 'select') {
        //         $form->valueSelect = $form->model()->value; 
        //     }
        // }

        $form->textarea ('desc',trans ('description'));
        $form->select('category', trans('category'))
         ->options(ConfigCategory::getTranslatedOptions())
         ->required();
        $form->select('type', trans('type'))
         ->options(ConfigType::getTranslatedOptions())
         ->required()
         ->when("select", function () use ($form) {
                $form->select('sub_type', trans('sub_type'))->options(function () {
                    $ops = ['1' => __("yes_or_no"),'2' => __("true_and_false")];
                    return $ops;
                }) ->when("1", function () use ($form) {
                    $ops = ['yes' => __("yes"),'no' => __("no")];
                    $form->select('valueSelect', trans('value'))->options($ops);
                })->when("2", function () use ($form) {
                    $ops = ['true' => __("true"),'false' => __("false")];
                    $form->select('valueSelect', trans('value'))->options($ops);
                });
            })
         ->when("integer", function () use ($form) {
                $form->number('valueInteger', trans('value'));
            })
         ->when("string", function () use ($form) {
                $form->text('value', trans('value'));
            });

            $form->saving(function (Form $form) {
                if ($form->type == 'integer') {
                    $form->value = $form->valueInteger;
                } elseif ($form->type == 'select') {
                    $form->value = $form->valueSelect;
                } 
            });

        return $form;
    }

}
