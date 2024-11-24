<?php

namespace App\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Enums\PaymentType;
use App\Models\PaymentCoin;
use App\Models\PaymentGateway;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;

class PaymentCoinController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'paymentCoin';
    public $permission_name = 'payment-coin';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PaymentCoin());

        $grid->column('id', __('Id'));
        $grid->column('title', __('title'));
        $grid->column('photo', __('Photo'))->image('', 50);
        $grid->column('الاجرائات')->display(function () {
            $url1 = url('admin/coins/' . $this->id);
            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>الكوينات</a>";
            return $button1;
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
        $show = new Show(PaymentCoin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('photo', __('Photo'));
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
        $form = new Form(new PaymentCoin());

        $form->select('title', trans('Title'))
         ->options(PaymentType::getTranslatedOptions())
         ->creationRules(['required', "unique:payment_coins,title,{{id}}"])->updateRules(['required', "unique:payment_coins,title,{{id}}"]);
        $form->image('photo', __('Photo'));

        return $form;
    }
}
