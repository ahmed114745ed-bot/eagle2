<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Charge;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Auth\Permission;
use App\Admin\Actions\UsersChargeAction;
use Encore\Admin\Controllers\HasResourceActions;

class UsersChargeController extends MainController
{
    use HasResourceActions;
    public $permission_name = 'charge-to-user';


    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        if (! \Encore\Admin\Facades\Admin::user()->can('*')) {
            Permission::check('browse-' . $this->permission_name);
        }
        return $content
            ->title(trans('charges'))
            ->body($this->grid());
    }




    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->disableRowSelector();

        $grid->filter(function (Grid\Filter $filter) {

            $filter->expand();

            $filter->disableIdFilter();
            $filter->equal('ID', __('ID'));

            $filter->where(function ($query) {
                $query->where('name', 'like', "%{$this->input}%");
            }, __('name'));

            $filter->where(function ($query) {

                $query->where('uuid', 'like', "%{$this->input}%");
            }, __('uuid'));
        });

        $grid->model()
            ->select('id', 'name', 'uuid', 'coins', 'di')
            ->with('profile')
            ->orderByDesc('id');

        $grid->id(__('ID'));



        $grid->column('name', trans('owner'))
            ->display(function ($name) {
                $uid = @$this->uuid;
                $path = @$this->profile?->avatar;
                $defaultImage = asset("images/businessman-icon.jpg");
                $url = getImagePath($path) ?? $defaultImage;

                if (!isImageExists($url)) {
                    $url = $defaultImage;
                }

                $image = handleShowImageWithTypes($this->id, $url, 40, 40);
                $showUrl = $this ? url("admin/users/{$this->id}") : 0;
                return "
                    <div style='display: flex; align-items: center; gap: 10px;'>
                        $image
                        <div>
                           <a href='{$showUrl}' style='text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;'>
                             <span style='text-decoration: underline; cursor: pointer;'>$name</span>
                            </a>
                            <span style='color: #aaa; font-size: smaller;'>UUID: $uid</span>
                        </div>
                    </div>";
            });

        $grid->column('di', __('coins'))->display(function ($coin) {
            $icon = asset('images/coin.jpg');
            $coin = (float) $coin;
            return "
                <div style='display: flex; align-items: center; gap: 5px;'>
                    <span>" . number_format($coin) . "</span>
                    <img src='{$icon}' alt='Coin' width='20' height='20'>

                </div>
            ";
        });
        // $grid->column('di', __('coins'))->display(function ($coin) {
        //     $icon = asset('images/coin.jpg'); // تأكد من وجود الصورة في هذا المسار
        //     $coin = (float) $coin;
        //     return "
        //         <div style='display: flex; align-items: center; gap: 5px;'>
        //             <span>" . number_format($coin) . "</span>
        //             <img src='{$icon}' alt='Coin' width='20' height='20'>

        //         </div>
        //     ";
        // });
        // $grid->column('di', __('coins'))->display(function ($coin) {
        //     $shippingCoins = \Cache::rememberForever('shipping_coins', function () {
        //         $setting =   Setting::where('key', 'shipping_coins')->first();
        //         return $setting?->value;
        //     });

        //     if ($shippingCoins) {
        //         $dollars = $this->coins / $shippingCoins;
        //         $numberFormatDollars = number_format($dollars);
        //     } else {
        //         $numberFormatDollars = __('please set agency coins in configs');
        //     }

        //     $icon = asset('images/coins.jpg');
        //     return "
        //         <div style='display: flex; align-items: center; gap: 5px;'>
        //             <span>" . $numberFormatDollars . "</span>
        //             <img src='{$icon}' alt='Coin' width='20' height='20'>

        //         </div>
        //     ";
        // });
        if (\Encore\Admin\Facades\Admin::user()->can('add-switch-' . $this->permission_name) || \Encore\Admin\Facades\Admin::user()->can('*') || \Encore\Admin\Facades\Admin::user()->can('history-switch-' . $this->permission_name)) {
            $grid->column('actions', __('Actions'))
                ->display(function () {

                    return (new UsersChargeAction())->setUserId($this->id)->render();
                })
                ->style('white-space: nowrap; width: 100px;');
        }

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableActions();

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
        $show = new Show(Charge::findOrFail($id));

        //        $show->id('ID');
        //        $show->charger_id('charger_id');
        //        $show->charger_type('charger_type');
        //        $show->user_id('user_id');
        //        $show->user_type('user_type');
        //        $show->amount('amount');
        //        $show->amount_type('amount_type');
        //        $show->created_at(trans('admin.created_at'));
        //        $show->updated_at(trans('admin.updated_at'));
        $this->extendShow($show);
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Charge);

        //        $form->display('ID');
        //        $form->text('charger_id', 'charger_id');
        //        $form->text('charger_type', 'charger_type');
        //        $form->text('user_id', 'user_id');
        //        $form->text('user_type', 'user_type');
        //        $form->text('amount', 'amount');
        //        $form->text('amount_type', 'amount_type');
        //        $form->display(trans('admin.created_at'));
        //        $form->display(trans('admin.updated_at'));

        return $form;
    }
}
