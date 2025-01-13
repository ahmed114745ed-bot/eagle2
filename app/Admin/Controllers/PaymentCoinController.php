<?php

namespace App\Admin\Controllers;


use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Enums\PaymentType;
use App\Models\PaymentCoin;
use App\Admin\Controllers\MainController;
use Encore\Admin\Layout\Content;

class PaymentCoinController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'paymentCoin';
    public $permission_name = 'payment-coin';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(trans('payment-coins'))
            ->body($this->grid()));
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
        return parent::show($id,$content
            ->title(trans('payment-coins'))
            ->body($this->detail($id)));
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
        return parent::edit($id,$content
            ->title(trans('payment-coins'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(trans('payment-coins'))
            ->body($this->form()));
    }
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
        $grid->column('photo', __('image'))->image('', 50);
        $grid->column(__('procedures'))->display(function () {
            $url1 = url('admin/coins/' . $this->id);
            $button1 = "<a href='{$url1}' class='btn btn-sm btn-info'>". __('coins') ."</a>";
            return $button1;
        });
        $status = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];
        
        $grid->column('status', __("status"))->switch($status);

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
        $status = [
            'on' => ['value' => 1, 'text' => 'open', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => 'close', 'color' => 'default'],
        ];
        $form->switch('status', __('status'))->states($status);

        return $form;
    }
}
