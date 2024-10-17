<?php

namespace Modules\CP\Http\Controllers\web;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Selectables\Gifts;
use App\Services\AppFeatureService;
use Modules\Events\Entities\TargetEvent;
use App\Admin\Controllers\MainController;
use App\Models\Vip;
use Encore\Admin\Controllers\AdminController;
use Modules\Events\Entities\ChargeTargetEvent;

class LevelController extends MainController
{
    protected $title = 'CpLevel';
    public $permission_name = 'cp-level';
    // public function __construct()
    // {
    //     (new AppFeatureService)->validateStatusEnable("target_events");
    // }
    protected function grid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where("type",3);

        $grid->column('id', __('Id'));
        $grid->column('type', __('Type'))->select(
            [
                1 => __('broadcaster'),
                2 => __('honor'),
                3=>__ ('cp'),
                4=>__ ('room'),
            ]
        );
        $grid->column('level', __('Level'))->editable();   
        $grid->column('exp', __('Exp'))->display(function ($column, Grid\Column $value) {
            $value = $value->getOriginal();
            return number_format($value);
        })->editable();
        $grid->column('img', __('Image'))->image('', '30');

        $grid->column('الاجرائات')->display(function () {
            $url1 = url('admin/cp-level-gifts/' . $this->id);

            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>   هداية </a>";

            return $button1 ;
        });

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
        $show = new Show(Vip::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('tile', __('Tile'));
        $show->field('value', __('value'));
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
        $form = new Form(new Vip());
        $form->hidden("type")->value(3);
        $form->textarea('name_ar', __('name_ar'));
        $form->textarea('name_en', __('name_en'));
        $form->number('level', __('Level'))->required();
        $form->number('exp', __('Exp'))->help(__('sender: 1 coin = 1 exp -- receiver: 1 coin = 1 exp'));
        $form->image('img', __('Image'))->name(function ($file) {
            return now()->timestamp . rand(0, 999) . '.' . $file->guessExtension();
        });
        return $form;
    }
}
