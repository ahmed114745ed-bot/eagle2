<?php

namespace App\Admin\Controllers;

use App\Models\Exchange;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;

class ExchangeController extends MainController
{
    /**
     * Title for current resource.
     *
     * @var string
     */


    public $permission_name = 'exchange';
    public function index(Content $content)
    {
        return $content
            ->title(trans('Exchanges'))
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
            ->title(trans('Exchanges'))
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
            ->title(trans('Exchanges'))
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
            ->title(trans('Exchanges'))
            ->body($this->form());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Exchange());

        $grid->column('id', __('ID'))->sortable();
        $grid->column('diamonds', __('diamonds'))->display(function ($usd) {

            $image = asset('images/coin.png'); // تأكد من أن الصورة موجودة

            return "<div style='display: flex; align-items: center; gap: 5px;'>
                        <span>{$usd}</span>
                        <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });
        $grid->column('value', __('value'))->display(function ($value) {
            $image = asset('images/dollar.jpg'); // Adjust path as needed
            return "<div style='display: flex; align-items: center; '>

                        <span>{$value}</span>
                          <img src='{$image}' alt='USD' width='20' height='20'>
                    </div>";
        });

        $grid->column('type', __('type'));
        $this->extendGrid ($grid);
        $grid->disableExport();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Exchange::findOrFail($id));

//        $show->field('id', __('ID'));
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
        $form = new Form(new Exchange());

        $form->display('id', __('ID'));
        $form->number('diamonds', __('diamonds'));
        $form->number('value', __('value'));
        $form->select('type', __('type'))->options (
            [
                0=>__('coin'),
                1=>__('silver'),
            ]
        );

        return $form;
    }
}
