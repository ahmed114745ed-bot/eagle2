<?php

namespace Modules\Wallet\Http\Controllers;

use App\Admin\Controllers\MainController;
use App\Models\Language;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Modules\Wallet\Entities\WalletTemplate;

class WalletTemplateController extends MainController
{
    public $permission_name = 'wallet-template';

    public function index(Content $content)
    {
        return parent::index($content
            ->title(__('Wallet Template'))
            ->body($this->grid()));
    }

    public function show($id, Content $content)
    {
        return parent::show($id, $content
            ->title(__('Wallet Template'))
            ->body($this->detail($id)));
    }

    public function edit($id, Content $content)
    {
        return parent::edit($id, $content
            ->title(__('Wallet Template'))
            ->body($this->form()->edit($id)));
    }

    public function create(Content $content)
    {
        return parent::create($content
            ->title(__('Wallet Template'))
            ->body($this->form()));
    }

    protected function grid()
    {
        $grid = new Grid(new WalletTemplate());

        $grid->column('id', __('ID'))->sortable();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(WalletTemplate::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }


    protected function form()
    {
        $form = new Form(new WalletTemplate());

        $languages = Language::where('is_enabled', 1)->get();

        $form->fieldset(__('Name'), function (Form $form) use ($languages) {
            foreach ($languages as $lang) {
                $code = $lang->code;
                $name = $lang->name;

                $form->text("title.{$code}", __("Name")."($name)")->rules('nullable');
            }
        });

        $form->select('type', __('Type'))
            ->options([
                'digital_wallet' => __('Digital Wallet'),
                'bank_account' => __(' Bank Account'),
            ])
            ->rules('required|in:digital_wallet,bank_account');

        Admin::script('
            $(".collapse.in").removeClass("in"); // Bootstrap 3
            $(".collapse.show").removeClass("show"); // Bootstrap 4/5
        ');

        return $form;
    }
}
