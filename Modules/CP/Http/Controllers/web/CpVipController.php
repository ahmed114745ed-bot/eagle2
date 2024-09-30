<?php

namespace Modules\CP\Http\Controllers\web;

use App\Models\Vip;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Controllers\MainController;


class CpVipController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'المستويات';

    public $permission_name = 'level-cp';
    public $hiddenColumns = [

    ];

    public function __construct ()
    {
        $this->title = __('Levels');
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Vip());
        $grid->model()->where('type',3)->orderBy('exp');

        $grid->quickSearch ();
        $grid->column('id', __('Id'));
        $grid->column('level', __('Level'))->editable ();
        $grid->column('exp', __('Exp'))->display(function($column, Grid\Column $value) {
        $value = $value->getOriginal();
            return number_format($value);
        })->editable();
//        $grid->column('di', __('Diamonds'));
//        $grid->column('co', __('Coins'));
        $grid->column('img', __('Image'))->image ('','30');
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
        $show = new Show(Vip::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'))->number ();
        $show->field('level', __('Level'))->number ();
        $show->field('exp', __('Exp'))->number ();
//        $show->field('di', __('Diamonds'))->number ();
//        $show->field('co', __('Coins'))->number ();
        $show->field('img', __('Image'))->image ();
//        $show->field('created_at', __('Created at'));
//        $show->field('updated_at', __('Updated at'));
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
        $form = new Form(new Vip());

        $form->hidden('type')->value(3);
        $form->number('level', __('Level'))->required();
        $form->number('exp', __('Exp'))->help (__('sender: 1 coin = 1 exp -- receiver: 1 coin = 1 exp'));
//        $form->number('di', __('Diamonds'));
//        $form->number('co', __('Coins'));
        $form->image ('img',__('Image'))->name(function ($file) {
            return now()->timestamp.rand(0,999).'.'.$file->guessExtension();
        });

        return $form;
    }
}
