<?php

namespace Modules\Events\Http\Controllers\web;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Selectables\Gifts;
use App\Services\AppFeatureService;
use Modules\Events\Entities\TargetEvent;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;
use Modules\Events\Entities\ChargeTargetEvent;

class TargetEventController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'TargetEvent';
    public function __construct()
    {
        (new AppFeatureService)->validateStatusEnable("target_events");
    }
    public $permission_name = 'event';
    protected function grid()
    {
//        dd(ChargeTargetEvent::with('rewards.ware')->first());
        $grid = new Grid(new ChargeTargetEvent());

        $grid->column('id', __('Id'));
        $grid->column('value', __('value'));
        $grid->column('الاجرائات')->display(function () {
            // توليد الروابط
            $url1 = url('admin/target-events-gift/' . $this->id);

            // إنشاء أزرار HTML
            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>   هداية </a>";

            // دمج الأزرار في سلسلة واحدة وإرجاعها
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
        $show = new Show(ChargeTargetEvent::findOrFail($id));

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
        $form = new Form(new ChargeTargetEvent());
        $form->number('value', __('value'));
        $form->saved(function (Form $form) {
            return redirect()->to('admin/target-events-gift/'.$form->model()->id);
        });
        return $form;
    }
}
