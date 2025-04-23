<?php

namespace App\Admin\Controllers;

use App\Models\Box;
use App\Models\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Helpers\Common;
use Encore\Admin\Layout\Content;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Controllers\HasResourceActions;

class BoxController extends MainController
{
    public $permission_name = 'boxes';
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title(trans('boxes'))
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
            ->title(trans('boxes'))
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
            ->title(trans('boxes'))
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
            ->title(trans('boxes'))
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Box);

        $grid->id(__('ID'));
        $grid->column('type', __('type'))->using([0 => __('normal'), 1 => __('super')]);
        $grid->column('coins', __('coins'))->display(function ($coins) {

            $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

            return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <span>{$coins}</span>
                        <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('users', __('users'));
        $grid->column('image', __('image'))->image('', 30);
        $grid->column('has_label', __('has_label'));
        $grid->column('duration', __('duration'));
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
        $show = new Show(Box::findOrFail($id));

        //        $show->id('ID');
        //        $show->type('type');
        //        $show->coins('coins');
        //        $show->users('users');
        //        $show->image('image');
        //        $show->has_label('has_label');
        //        $show->duration('duration');
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
        $form = new Form(new Box);

        $form->display(__('ID'));
        $form->select('type', __('type'))
            ->options([0 => __('normal'), 1 => __('super')])
            ->attribute(['id' => 'box_type'])
            ->required();

        $form->decimal('coins', __('coins'));
        $form->decimal('users', __('users'))->attribute(['id' => 'users_field']);
        $form->decimal('duration', __('duration'))->help(__('in minutes'))->attribute(['id' => 'duration_field']);
       
        $form->html(<<<HTML
                <div id="dynamic_fields_container">
                    <div class="dynamic-field-group" style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                        <input type="number" name="dynamic_fields[]" class="form-control" placeholder="أدخل قيمة رقمية" style="flex: 1;">
                        <button type="button" class="btn btn-danger remove-field">حذف</button>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" id="add_field" class="btn btn-primary" style="margin-top: 10px;">
                        إضافة حقل جديد
                    </button>
                </div>
            HTML);

        $form->image('image', __('image'));
        $form->switch('has_label', __('has label'))->states(Common::getSwitchStates());
        $form->text('default_label', __('default label'));
        $form->html(<<<HTML
        <script>
           $(document).ready(function () {
                initDynamicFieldsScript();
            });
        </script>
        HTML);
        
        
        $form->saving(function (Form $form) {
            $dynamicFields = request('dynamic_fields', []);
            $combinedValues = implode(',', array_filter($dynamicFields));
            $form->model()->dynamic_users_values = $combinedValues;
        });

        return $form;
    }

    public function box_settings(Content $content)
    {
        $config = Config::whereIn('name', ['app_wallet_lucky_box', 'normal_box_duration'])->pluck('value', 'name')->toArray();
        return $content->view('box_settings', compact('config'));
    }
}
