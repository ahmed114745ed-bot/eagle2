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

        $form->number('coins', __('coins'));
        $form->number('users', __('users'))->attribute(['id' => 'users_field']);
        $form->number('duration', __('duration'))->help(__('in minutes'))->attribute(['id' => 'duration_field']);
        $form->image('image', __('image'));
        $form->switch('has_label', __('has label'))->states(Common::getSwitchStates());
        $form->text('default_label', __('default label'));

        // Fixed JS
        $script = <<<SCRIPT
        $(document).ready(function() {
            function toggleFields() {
                var type = $('#box_type').val();
                if (type == '0') {
                    $('#users_field').closest('.form-group').show();
                    $('#duration_field').closest('.form-group').show();
                } else {
                    $('#users_field').closest('.form-group').hide();
                    $('#duration_field').closest('.form-group').hide();
                }
            }

            toggleFields(); // on page load

            $('#type').change(function() {
                toggleFields(); // on select change
            });
        });
    SCRIPT;

        Admin::script($script);
        $form->saving(function (Form $form) {
            $normalDuration = Common::getConf('normal_box_duration') ?? 1;

            if ($form->type == 0) {
                $form->duration =  $normalDuration;
            }
        });


        return $form;
    }


    public function box_settings(Content $content)
    {
        $config = Config::whereIn('name', ['app_wallet_lucky_box', 'normal_box_duration'])->pluck('value', 'name')->toArray();
        return $content->view('box_settings', compact('config'));
    }
}
